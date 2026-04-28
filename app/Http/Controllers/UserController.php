<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private User $user,
        private UserRepository $userRepository,
    ) { }

    public function select()
    {
        return $this->user
            ->query()
            ->when(request()->query('ids'), function($query, $ids) {
                $ids = explode(',', $ids);
                $query->whereIn('id', $ids);
            })
            ->when(request()->query('q'), function($query, $q) {
                $query->where('name', 'LIKE', "%$q%");
            })
            ->cursorPaginate(request()->query('perPage', 10));
    }

    public function index(Request $request)
    {
        return $this->userRepository
            ->paginate(
                '/user',
                $request->query('search'),
                null,
                $request->collect('orders'),
            );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('users', 'name')],
            'email' => ['required', 'string', 'email', Rule::unique('users', 'email')],
            'password' => ['nullable', 'string', 'min:4', 'confirmed'],
        ]);

        $user = $this->userRepository->save($validated);

        return response()->json([
            'message' => 'Your Data Successfully created',
            'user' => $user,
        ], 201);
    }

    public function show(User $user)
    {
        return ['data' => $user];
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', Rule::unique('users', 'name')->ignoreModel($user)],
            'email' => ['required', 'string', 'email', Rule::unique('users', 'email')->ignoreModel($user)],
            'password' => ['nullable', 'string', 'min:4', 'confirmed'],
        ]);

        $validated['id'] = $user->id;
        $user = $this->userRepository->save($validated);

        return response()->json([
            'message' => 'Your Data Successfully updated',
            'user' => $user,
        ], 201);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'message' => 'Your Data Successfully deleted',
            'user' => $user,
        ]);
    }
}
