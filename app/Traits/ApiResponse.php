<?php

namespace App\Traits;

use App\Enums\StatusEnum;

trait ApiResponse
{
    /**
     * Formater la réponse API
     *
     * @param StatusEnum $status
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function sendResponse(StatusEnum $status, $data = null, $message = '')
    {
        $httpStatus = $this->getHttpStatusFromEnum($status);

        return response()->json([
            'status' => $status->value,
            'data' => $data,
            'message' => $message
        ], $httpStatus);
    }

    /**
     * Convertir StatusEnum en code de statut HTTP
     *
     * @param StatusEnum $status
     * @return int
     */
    private function getHttpStatusFromEnum(StatusEnum $status): int
    {
        return match($status) {
            StatusEnum::SUCCESS => 200,
            StatusEnum::ERROR => 400,
        };
    }
}
