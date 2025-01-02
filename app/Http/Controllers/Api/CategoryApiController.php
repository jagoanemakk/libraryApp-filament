<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $category = Category::latest()->paginate(10);

        return new CategoryResource(true, 'List category data', $category);
    }

    public function show($id)
    {
        $category = Category::find($id);

        return new CategoryResource(true, 'Single data category', $category);
    }
}
