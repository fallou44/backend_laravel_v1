<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\RegisterUserRequest;
use Illuminate\Http\Request;
use App\Enums\RoleEnum;
use App\Enums\StatusEnum;
use Spatie\QueryBuilder\QueryBuilder;
use App\Models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="Points de terminaison API pour la gestion des utilisateurs"
 * )
 */
class UserController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Obtenir la liste des utilisateurs avec des filtres optionnels",
     *     description="Récupère une liste paginée des utilisateurs avec possibilité de filtrer par rôle et statut actif",
     *     @OA\Parameter(
     *         name="role",
     *         in="query",
     *         description="Filtre les utilisateurs par rôle",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtre les utilisateurs par statut actif",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des utilisateurs récupérée avec succès",
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
        try {
            $users = QueryBuilder::for(User::class)
                ->when($request->has('role'), function ($query) use ($request) {
                    $query->whereRaw('LOWER(role) = ?', [strtolower($request->input('role'))]);
                })
                ->when($request->has('active'), function ($query) use ($request) {
                    $query->where('etat', $request->input('active') === 'oui');
                })
                ->allowedSorts(['role', 'created_at', 'name'])
                ->paginate($request->input('per_page', 15));

            return $this->sendResponse(
                StatusEnum::SUCCESS,
                $users,
                'Liste des utilisateurs récupérée avec succès'
            );
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la récupération des utilisateurs: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Obtenir les informations d'un utilisateur spécifique",
     *     description="Récupère les détails d'un utilisateur en fonction de son ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur à récupérer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations de l'utilisateur récupérées avec succès",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return $this->sendResponse(StatusEnum::SUCCESS, $user, 'Utilisateur récupéré avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Utilisateur non trouvé', 404);
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la récupération de l\'utilisateur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     tags={"Users"},
     *     summary="Créer un nouvel utilisateur",
     *     description="Crée un nouvel utilisateur avec les informations fournies",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreUserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation des données",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $validatedData['mot_de_passe'] = bcrypt($validatedData['mot_de_passe']);
            $validatedData['role'] = RoleEnum::from($validatedData['role']);

            $user = User::create($validatedData);

            return $this->sendResponse(StatusEnum::SUCCESS, $user, 'Utilisateur créé avec succès');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation des données', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/register",
     *     tags={"Authentication"},
     *     summary="Enregistrer un nouvel utilisateur et l'associer à un client existant",
     *     description="Crée un nouvel utilisateur et l'associe à un client existant dans la base de données",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/RegisterUserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur enregistré et associé au client avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation ou client déjà associé à un utilisateur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur lors de l'enregistrement",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function register(RegisterUserRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $client = Client::findOrFail($validatedData['client_id']);

            if ($client->user()->exists()) {
                return $this->sendResponse(StatusEnum::ERROR, null, 'Le client est déjà associé à un utilisateur', 422);
            }

            $validatedData['mot_de_passe'] = Hash::make($validatedData['mot_de_passe']);
            $user = User::create($validatedData);
            $client->user()->associate($user);
            $client->save();

            return $this->sendResponse(StatusEnum::SUCCESS, $user, 'Utilisateur enregistré et associé au client avec succès');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation des données', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Client non trouvé', 422);
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de l\'enregistrement de l\'utilisateur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Mettre à jour les informations d'un utilisateur",
     *     description="Met à jour les informations d'un utilisateur spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur à mettre à jour",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation des données",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(UpdateUserRequest $request, $id)
    {
        try {
            $validatedData = $request->validated();
            $user = User::findOrFail($id);

            $user->update($validatedData);

            return $this->sendResponse(StatusEnum::SUCCESS, $user, 'Utilisateur mis à jour avec succès');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur de validation des données', 422, $e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Utilisateur non trouvé', 404);
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la mise à jour de l\'utilisateur: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     tags={"Users"},
     *     summary="Supprimer un utilisateur",
     *     description="Supprime un utilisateur spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur à supprimer",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Utilisateur supprimé avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Utilisateur non trouvé', 404);
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la suppression de l\'utilisateur: ' . $e->getMessage(), 500);
        }
    }
}
