<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UserValidation;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use App\Rules\CustomPassword;
use App\Rules\PhoneNumber;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints of User management"
 * )
 */
class UserController extends Controller
{
 /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Get list of users with optional filters",
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/User")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('active')) {
            $etat = $request->active === 'oui';
            $query->where('etat', $etat);
        }

        $users = $query->get();

        return $this->sendResponse(StatusEnum::SUCCESS, $users, 'Users retrieved successfully');
    }


    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Get user information",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->sendResponse(StatusEnum::SUCCESS, $user, 'User retrieved successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'User not found');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Create a new user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StoreUserRequest $request)
    {
        $validatedData = $request->validated();

        $validatedData['mot_de_passe'] = bcrypt($validatedData['mot_de_passe']);
        $validatedData['role'] = RoleEnum::from($validatedData['role']);

        $user = User::create($validatedData);

        return $this->sendResponse(StatusEnum::SUCCESS, $user, 'User created successfully');
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Update an existing user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $validatedData = $request->validated();

        if (isset($validatedData['mot_de_passe'])) {
            $validatedData['mot_de_passe'] = bcrypt($validatedData['mot_de_passe']);
        }

        if (isset($validatedData['role'])) {
            $validatedData['role'] = RoleEnum::from($validatedData['role']);
        }

        $user->update($validatedData);

        return $this->sendResponse(StatusEnum::SUCCESS, $user, 'User updated successfully');
    }




    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Delete a user",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->sendResponse(StatusEnum::SUCCESS, null, 'User deleted successfully');
    }
}
