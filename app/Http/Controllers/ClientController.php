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
use Carbon\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Clients",
 *     description="Points de terminaison pour la gestion des clients"
 * )
 */
class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/wane/v1/clients",
     *     tags={"Clients"},
     *     summary="Obtenir la liste des clients",
     *     description="Récupère une liste paginée des clients avec possibilité de filtrage, tri et inclusion de relations",
     *     @OA\Parameter(
     *         name="telephone",
     *         in="query",
     *         description="Filtrer par numéros de téléphone (séparés par des virgules)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Trier par champ (préfixer par - pour un ordre décroissant)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="include",
     *         in="query",
     *         description="Inclure les relations (ex: user)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="comptes",
     *         in="query",
     *         description="Filtrer les clients avec ou sans comptes (oui|non)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Filtrer les clients avec des comptes actifs ou inactifs (oui|non)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"oui", "non"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de la page pour la pagination",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des clients récupérée avec succès",
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function index(Request $request)
    {

        $this->authorize('view', Client::class);
        try {
            $this->authorize('viewAny', Client::class);

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

            $paginatedClients = $clients->paginate(15);

            return $this->sendResponse(
                StatusEnum::SUCCESS,
                new ClientCollection($paginatedClients),
                'Clients récupérés avec succès'
            );
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clients: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des clients', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/wane/v1/clients/{id}/user",
     *     tags={"Clients"},
     *     summary="Afficher les informations du client avec le compte utilisateur associé",
     *     description="Récupère les informations détaillées d'un client, y compris son compte utilisateur associé",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du client récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function showUserInfo($id)
    {

        $this->authorize('view', Client::class);
        try {
            $client = Client::with('user')->findOrFail($id);

            return $this->sendResponse(
                StatusEnum::SUCCESS,
                new ClientResource($client),
                'Informations du client avec le compte utilisateur récupérées avec succès'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Client non trouvé', 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des informations du client: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des informations du client', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/wane/v1/clients/{id}/dettes",
     *     tags={"Clients"},
     *     summary="Lister les dettes d'un client",
     *     description="Récupère la liste des dettes d'un client spécifique sans détails",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des dettes récupérée avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="montant_total", type="number"),
     *                     @OA\Property(property="date_echeance", type="string", format="date"),
     *                     @OA\Property(property="statut", type="string")
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function listDettes($id)
    {
        $this->authorize('view', Client::class);
        try {
            $client = Client::with('dettes')->findOrFail($id);

            $dettes = $client->dettes->map(function ($dette) {
                $dateEcheance = $dette->date_echeance instanceof Carbon ? $dette->date_echeance : Carbon::parse($dette->date_echeance);

                return [
                    'id' => $dette->id,
                    'montant_total' => $dette->montant_total,
                    'date_echeance' => $dateEcheance->format('Y-m-d'),
                    'statut' => $dette->statut
                ];
            });

            return $this->sendResponse(
                StatusEnum::SUCCESS,
                $dettes,
                'Dettes du client récupérées avec succès'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Client non trouvé', 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des dettes du client: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération des dettes du client', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Obtenir les informations d'un client",
     *     description="Récupère les informations détaillées d'un client spécifique",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du client",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Informations du client récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function show($id)
    {
        $this->authorize('view', Client::class);
        try {
            $client = Client::findOrFail($id);
            return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client récupéré avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Client non trouvé', 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du client: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la récupération du client', 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/wane/v1/clients",
     *     tags={"Clients"},
     *     summary="Créer un nouveau client",
     *     description="Crée un nouveau client avec ou sans utilisateur associé",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surnom", "telephone"},
     *             @OA\Property(property="surnom", type="string", description="Surnom unique du client"),
     *             @OA\Property(property="telephone", type="string", description="Numéro de téléphone du client"),
     *             @OA\Property(property="user", type="object", required={"email", "password"},
     *                 @OA\Property(property="email", type="string", description="Adresse e-mail de l'utilisateur"),
     *                 @OA\Property(property="password", type="string", description="Mot de passe de l'utilisateur")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client créé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function store(ClientStoreRequest $request)
    {
        $currentUser = auth()->user(); // Récupérer l'utilisateur actuellement connecté
        $roleToCreate = $request->input('user.role', 'CLIENT'); // Récupérer le rôle à créer (défaut à 'CLIENT')

        // Vérifiez si l'utilisateur a la permission de créer ce rôle
        if (!$this->create($currentUser, $roleToCreate)) {
            return $this->sendResponse(StatusEnum::ERROR, null, 'Vous n\'avez pas la permission de créer ce rôle', 403);
        }

        DB::beginTransaction();

        try {
            $client = Client::create($request->only(['surnom', 'telephone']));
            if ($request->filled('user')) {
                $user = User::create([
                    'email' => $request->input('user.email'),
                    'password' => Hash::make($request->input('user.password')),
                    'role' => $roleToCreate, // Assignez le rôle à l'utilisateur
                ]);
                $client->user()->associate($user);
                $client->save();
            }
            DB::commit();

            return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client créé avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du client: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la création du client', 500);
        }
    }


    /**
     * @OA\Put(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Mettre à jour un client",
     *     description="Met à jour les informations d'un client existant et son utilisateur associé si fourni",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du client à mettre à jour",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surnom", "telephone"},
     *             @OA\Property(property="surnom", type="string", description="Nouveau surnom du client"),
     *             @OA\Property(property="telephone", type="string", description="Nouveau numéro de téléphone du client"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="email", type="string", description="Nouvelle adresse e-mail de l'utilisateur"),
     *                 @OA\Property(property="password", type="string", description="Nouveau mot de passe de l'utilisateur")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(property="data", ref="#/components/schemas/Client"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function update(ClientUpdateRequest $request, $id)
    {
        $this->authorize('update', Client::class);
        DB::beginTransaction();

        try {
            $client = Client::findOrFail($id);
            $client->update($request->only(['surnom', 'telephone']));

            if ($request->filled('user')) {
                $user = $client->user;
                if (!$user) {
                    $user = new User();
                    $client->user()->associate($user);
                }
                $user->email = $request->input('user.email');
                if ($request->filled('user.password')) {
                    $user->password = Hash::make($request->input('user.password'));
                }
                $user->save();
            }

            DB::commit();

            return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client mis à jour avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->sendResponse(StatusEnum::ERROR, null, 'Client non trouvé', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du client: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la mise à jour du client', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Supprimer un client",
     *     description="Supprime un client existant et son utilisateur associé",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du client à supprimer",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client supprimé avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS"}),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur"
     *     )
     * )
     */
    public function destroy($id)
    {
        $this->authorize('delete', Client::class);
        DB::beginTransaction();

        try {
            $client = Client::findOrFail($id);

            if ($client->user) {
                $client->user->delete();
            }

            $client->delete();

            DB::commit();

            return $this->sendResponse(StatusEnum::SUCCESS, null, 'Client supprimé avec succès');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return $this->sendResponse(StatusEnum::ERROR, null, 'Client non trouvé', 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du client: ' . $e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Une erreur est survenue lors de la suppression du client', 500);
        }
    }
}
