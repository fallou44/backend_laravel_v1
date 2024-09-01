<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Http\Requests\ClientStoreRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;
use App\Enums\StatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="wane Endpoints of Client management"
 * )
 */
class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/wane/v1/clients",
     *     tags={"Clients"},
     *     summary="Get list of clients with optional filters, sorting, and includes",
     *     @OA\Parameter(
     *         name="telephone",
     *         in="query",
     *         description="Filter by telephone numbers (comma-separated)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort by field (prefix with - for descending order)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Include related models",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="comptes",
     *         in="query",
     *         description="Filter clients with or without accounts (oui|non)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Client")),
     *                 @OA\Property(property="links", type="object"),
     *                 @OA\Property(property="meta", type="object")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $clients = QueryBuilder::for(Client::class)
            ->when($request->has('telephone'), function ($query) use ($request) {
                $telephones = explode(',', $request->input('telephone'));
                $query->whereIn('telephone', $telephones);
            })
            ->when($request->has('comptes'), function ($query) use ($request) {
                $query->where('user_id', $request->input('comptes') === 'oui' ? '!=' : '=', null);
            })
            ->when($request->has('active'), function ($query) use ($request) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('etat', $request->input('active') === 'oui');
                });
            })
            ->allowedSorts(['telephone', 'surnom', 'created_at'])
            ->allowedIncludes(['user']);

        // Apply pagination
        $paginatedClients = $clients->paginate(15);

        return $this->sendResponse(
            StatusEnum::SUCCESS,
            new ClientCollection($paginatedClients),
            'Clients retrieved successfully'
        );
    }







    /**
     * @OA\Get(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Get client information",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found"
     *     )
     * )
     */
    public function show($id)
    {
        $client = Client::with('user')->findOrFail($id);
        return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/wane/v1/clients",
     *     tags={"Clients"},
     *     summary="Create a new client with or without associated user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surnom", "telephone"},
     *             @OA\Property(property="surnom", type="string", description="Unique nickname for the client"),
     *             @OA\Property(property="telephone", type="string", description="Unique phone number for the client"),
     *             @OA\Property(property="adresse", type="string", nullable=true, description="Client's address"),
     *             @OA\Property(property="user", type="object", nullable=true,
     *                 @OA\Property(property="prenom", type="string", description="User's first name"),
     *                 @OA\Property(property="nom", type="string", description="User's last name"),
     *                 @OA\Property(property="email", type="string", format="email", description="Unique email for the user"),
     *                 @OA\Property(property="mot_de_passe", type="string", format="password", description="User's password (min 8 characters, mixed case, numbers, and symbols)"),
     *                 @OA\Property(property="mot_de_passe_confirmation", type="string", format="password", description="Confirmation of the user's password"),
     *                 @OA\Property(property="role", type="string", enum={"CLIENT"}, description="User's role")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"ERROR"}),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(ClientStoreRequest $request)
    {
        try {
            $client = DB::transaction(function () use ($request) {
                $clientData = $request->only(['surnom', 'telephone', 'adresse']);
                $userId = null;

                if ($request->has('user')) {
                    $userData = $request->get('user');
                    $userData['mot_de_passe'] = Hash::make($userData['mot_de_passe']);
                    $user = User::create($userData);
                    $userId = $user->id;
                }

                if ($userId) {
                    $clientData['user_id'] = $userId;
                }

                $client = Client::create($clientData);
                return $client->load('user');
            });

            return $this->sendResponse(
                StatusEnum::SUCCESS,
                new ClientResource($client),
                'Client ' . ($request->has('user') ? 'and associated user ' : '') . 'created successfully'
            );
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Error creating client: ' . $e->getMessage());
        }
    }



    /**
     * @OA\Patch(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Update an existing client",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(ClientUpdateRequest $request, Client $client)
    {
        try {
            DB::transaction(function () use ($request, $client) {
                $client->update($request->only(['surnom', 'telephone', 'adresse']));

                if ($request->has('user')) {
                    $userData = $request->input('user');
                    if (isset($userData['mot_de_passe'])) {
                        $userData['mot_de_passe'] = Hash::make($userData['mot_de_passe']);
                    }
                    $client->user()->updateOrCreate(
                        ['id' => $client->user_id],
                        $userData
                    );
                }
            });

            $client->load('user');
            return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client updated successfully');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Error updating client: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Delete a client",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found"
     *     )
     * )
     */
    public function destroy(Client $client)
    {
        try {
            DB::transaction(function () use ($client) {
                // Si un utilisateur est associé, il sera automatiquement supprimé grâce à la contrainte de clé étrangère onDelete('cascade')
                $client->delete();
            });
            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Client deleted successfully');
        } catch (\Exception $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Error deleting client: ' . $e->getMessage());
        }
    }
}
