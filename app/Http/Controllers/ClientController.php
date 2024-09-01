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
     *     summary="Obtenir la liste des clients avec filtres, tri, et inclusions optionnels",
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
     *         description="Inclure les modèles associés",
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
     *         name="page",
     *         in="query",
     *         description="Numéro de la page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
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

        // Appliquer la pagination
        $paginatedClients = $clients->paginate(15);

        return $this->sendResponse(
            StatusEnum::SUCCESS,
            new ClientCollection($paginatedClients),
            'Clients récupérés avec succès'
        );
    }

    /**
     * @OA\Get(
     *     path="/wane/v1/clients/{id}/user",
     *     tags={"Clients"},
     *     summary="Afficher les informations du client avec le compte utilisateur associé",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", enum={"SUCCESS", "ERROR"}),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 ref="#/components/schemas/Client"
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     */
    public function showUserInfo($id)
    {
        $client = Client::with('user')->findOrFail($id);

        return $this->sendResponse(
            StatusEnum::SUCCESS,
            new ClientResource($client),
            'Informations du client avec le compte utilisateur récupérées avec succès'
        );
    }

/**
 * @OA\Get(
 *     path="/wane/v1/clients/{id}/dettes",
 *     tags={"Clients"},
 *     summary="Lister les dettes d'un client sans détails",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Opération réussie",
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
 *     )
 * )
 */
public function listDettes($id)
{
    // Récupérer le client et ses dettes associées
    $client = Client::with('dettes')->findOrFail($id);

    // Extraire les dettes du client
    $dettes = $client->dettes->map(function($dette) {
        // Vérifiez si date_echeance est une chaîne et convertissez-la en instance Carbon si nécessaire
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
}



    /**
     * @OA\Get(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Obtenir les informations d'un client",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Opération réussie",
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
     *     )
     * )
     */
    public function show($id)
    {
        $client = Client::findOrFail($id);
        return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client récupéré avec succès');
    }

    /**
     * @OA\Post(
     *     path="/wane/v1/clients",
     *     tags={"Clients"},
     *     summary="Créer un nouveau client avec ou sans utilisateur associé",
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
     *         description="Requête invalide"
     *     )
     * )
     */
    public function store(ClientStoreRequest $request)
    {
        DB::beginTransaction();

        try {
            $client = Client::create($request->only(['surnom', 'telephone']));
            if ($request->filled('user')) {
                $user = User::create([
                    'email' => $request->input('user.email'),
                    'password' => Hash::make($request->input('user.password')),
                ]);
                $client->user()->associate($user);
                $client->save();
            }
            DB::commit();

            return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client créé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du client: '.$e->getMessage());
            return $this->sendResponse(StatusEnum::ERROR, null, 'Erreur lors de la création du client');
        }
    }

    /**
     * @OA\Put(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Mettre à jour les informations d'un client",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"surnom", "telephone"},
     *             @OA\Property(property="surnom", type="string", description="Surnom unique du client"),
     *             @OA\Property(property="telephone", type="string", description="Numéro de téléphone du client"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="email", type="string", description="Adresse e-mail de l'utilisateur"),
     *                 @OA\Property(property="password", type="string", description="Mot de passe de l'utilisateur")
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
     *     )
     * )
     */
    public function update(ClientUpdateRequest $request, $id)
    {
        $client = Client::findOrFail($id);
        $client->update($request->only(['surnom', 'telephone']));

        if ($request->filled('user')) {
            $user = $client->user;
            $user->update([
                'email' => $request->input('user.email'),
                'password' => Hash::make($request->input('user.password')),
            ]);
        }

        return $this->sendResponse(StatusEnum::SUCCESS, new ClientResource($client), 'Client mis à jour avec succès');
    }

    /**
     * @OA\Delete(
     *     path="/wane/v1/clients/{id}",
     *     tags={"Clients"},
     *     summary="Supprimer un client",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Client supprimé avec succès"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client non trouvé"
     *     )
     * )
     */
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return $this->sendResponse(StatusEnum::SUCCESS, null, 'Client supprimé avec succès');
    }
}
