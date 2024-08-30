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

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="API Endpoints of Client management"
 * )
 */
class ClientController extends Controller
{
  /**
     * @OA\Get(
     *     path="/api/v1/clients",
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
        $query = Client::query();

        // Filtre par numéro de téléphone
        if ($request->has('telephone')) {
            $telephones = explode(',', $request->input('telephone'));
            $query->whereIn('telephone', $telephones);
        }

        // Tri
        if ($request->has('sort')) {
            $sortField = $request->input('sort');
            $sortDirection = 'asc';
            if (strpos($sortField, '-') === 0) {
                $sortField = substr($sortField, 1);
                $sortDirection = 'desc';
            }
            $query->orderBy($sortField, $sortDirection);
        }

        // Inclusion de la relation utilisateur
        if ($request->has('include') && $request->input('include') === 'user') {
            $query->with('user');
        }

        $clients = $query->paginate(15);

        return $this->sendResponse(
            StatusEnum::SUCCESS,
            new ClientCollection($clients),
            'Clients retrieved successfully'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/clients/{id}",
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
     *     path="/api/v1/clients",
     *     tags={"Clients"},
     *     summary="Create a new client with or without associated user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surnom", "telephone"},
     *             @OA\Property(property="surnom", type="string"),
     *             @OA\Property(property="telephone", type="string"),
     *             @OA\Property(property="adresse", type="string", nullable=true),
     *             @OA\Property(property="user", type="object", nullable=true,
     *                 @OA\Property(property="prenom", type="string"),
     *                 @OA\Property(property="nom", type="string"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="mot_de_passe", type="string"),
     *                 @OA\Property(property="role", type="string")
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
     *         description="Validation error"
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
                    $userData = $request->get('user'); // Utilisation de get au lieu de input
                    $userData['mot_de_passe'] = Hash::make($userData['mot_de_passe']);
                    $user = User::create($userData);
                    $userId = $user->id;
                }

                // Ajouter l'user_id au clientData seulement si un utilisateur a été créé
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
     *     path="/api/v1/clients/{id}",
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
     *     path="/api/v1/clients/{id}",
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
