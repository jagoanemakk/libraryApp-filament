<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoansResource;
use App\Models\Books;
use App\Models\Loans;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoansApiControllers extends Controller
{
    public function index()
    {
        $loans = Loans::latest()->paginate(10);

        return new LoansResource(true, "List data loans", $loans);
    }

    public function show($id)
    {
        $loans = Loans::find($id);

        return new LoansResource(true, "Single data loans", $loans);
    }

    public function store(Request $request, Books $books, Loans $loans)
    {
        $totalLoans = Loans::whereNull('deletes_by')->count();

        $userRole = auth()->user()->roles->pluck('name')->first();

        DB::beginTransaction();

        $validated = $request->validate([
            'books_id' => 'required|exists:books,id',
            'due_date' => 'required|date',
        ]);

        $books = Books::find($validated['books_id']);

        try {
            if ($userRole === "Member" && $totalLoans > 2) {
                DB::rollBack();
                return response()->json([
                    'message' => 'You have reach limit of loans',
                ], 422);
            } else if ($books->qty <= 0) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Unavailable',
                ], 422);
            } else {
                $loans = new Loans();
                $loans->user_id = auth()->user()->id;
                $loans->books_id = $books->id;
                $loans->due_date = $validated['due_date'];
                $loans->save();

                $books->decrement('qty');

                DB::commit();

                return response()->json([
                    'message' => 'Create loans successfully',
                    'data' => $loans,
                ], 201);
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                500
            ]);
        }
    }
}
