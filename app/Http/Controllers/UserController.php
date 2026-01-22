<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private User $user,
    ) { }

    public function select()
    {
        return $this->user
            ->query()
            ->when(request()->query('q'), function($query, $q) {
                $query->where('name', 'LIKE', "%$q%");
            })
            ->cursorPaginate(request()->query('perPage', 10));
    }

    public function index()
    {
        return $this->user
            ->query()
            ->paginate(request()->query('perPage', 10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
