<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\TrialLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfinitePayWebhookController extends Controller
{
    public function __invoke(Request $request, TrialLifecycleService $trialLifecycleService): JsonResponse
    {
        $orderNsu = (string) $request->input('order_nsu', '');

        if ($orderNsu === '') {
            return response()->json([
                'success' => false,
                'message' => 'order_nsu is required.',
            ], 400);
        }

        $company = $this->resolveCompanyFromOrderNsu($orderNsu);

        if ($company === null) {
            return response()->json([
                'success' => false,
                'message' => 'Company not found for this order_nsu.',
            ], 400);
        }

        $trialLifecycleService->markCompanyAsPaid($company, [
            'invoice_slug' => (string) $request->input('invoice_slug', ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => null,
        ]);
    }

    private function resolveCompanyFromOrderNsu(string $orderNsu): ?Company
    {
        $parsedId = null;

        if (preg_match('/^aura-(\d+)-/i', $orderNsu, $matches) === 1) {
            $parsedId = (int) ($matches[1] ?? 0);
        }

        if ($parsedId !== null && $parsedId > 0) {
            $company = Company::query()->find($parsedId);

            if ($company !== null) {
                return $company;
            }
        }

        return Company::query()
            ->where('infinitepay_order_nsu', $orderNsu)
            ->first();
    }
}

