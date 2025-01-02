<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BooksResource;
use App\Models\Books;

class BooksApiController extends Controller
{
    public function index()
    {
        $books = Books::latest()->paginate(10);

        return new BooksResource(true, 'List books data', $books);
    }

    public function show($id)
    {
        $books = Books::find($id);

        return new BooksResource(true, 'Single data books', $books);
    }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, string $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(string $id)
    // {
    //     //
    // }
}
