<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

    public function index(Request $request)
    {
        $datas = $this->user
            ->query()
            ->when($request->query('search'), function ($query, $search) {
                $query->where('name', 'like', "%$search%");
                $query->orWhere('email', 'like', "%$search%");
            })
            ->paginate(request()->query('perPage', 10))
            ->withQueryString()
            ->onEachSide(3)
            ->withPath('/user');
        return compact(['datas']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255', 'min:8']
        ]);

        DB::beginTransaction();

        try {
            $this->user->create($validated);

            DB::commit();

            return response()->json([
                "message" => "The user was successfully created.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th);

            return response()->json([
                "message" => "An error occurred while creating the user, contact admin."
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return compact(['user']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', 'min:3', Rule::unique('users', 'name')->ignoreModel($user)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignoreModel($user)],
            'password' => ['nullable', 'string', 'max:255', 'min:8']
        ]);

        DB::beginTransaction();

        try {
            $user->fill($request->only(['name', 'email']));

            if ($request->has('password')) {
                $user->password = $request->input('password');
            }

            $user->save();


            DB::commit();

            return response()->json([
                "message" => "The user was successfully updated.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th);

            return response()->json([
                "message" => "An error occurred while updating the user, contact admin."
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        DB::beginTransaction();

        try {
            $user->delete();

            DB::commit();

            return response()->json([
                "message" => "The user was successfully deleted.",
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error($th);

            return response()->json([
                "message" => "An error occurred while deleting the user, contact admin."
            ], 500);
        }
    }
}
