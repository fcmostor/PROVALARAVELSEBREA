${}<?php

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

$validCnpj = static function (string $value): bool {
    $cnpj = preg_replace('/\D/', '', $value);
    if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
        return false;
    }
    foreach ([12, 13] as $position) {
        $weights = $position === 12
            ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
            : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        
        foreach ($weights as $index => $weight) {
            $sum += (int) $cnpj[$index] * $weight;
        }
        $remainder = $sum % 11;
        if ((int) $cnpj[$position] !== ($remainder < 2 ? 0 : 11 - $remainder)) {
            return false;
        }
    }
    return true;
};

Route::prefix('v1')->group(function () use ($validCnpj): void {
    // GET /api/v1/cnpj/{cnpj}: consulta a fonte e sincroniza o registro local.
    Route::get('/cnpj/{cnpj}', function (string $cnpj) use ($validCnpj) {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (! $validCnpj($cnpj)) {
            return response()->json(['message' => 'CNPJ inválido.'], 422);
        }

        try {
            $response = Http::acceptJson()->connectTimeout(3)->timeout(8)
                ->get('https://brasilapi.com.br/api/cnpj/v1/'.$cnpj);
        } catch (ConnectionException $exception) {
            return response()->json(['message' => 'Serviço externo indisponível.'], 503);
        }
        if ($response->status() === 404) {
            return response()->json(['message' => 'CNPJ não encontrado na fonte externa.'], 404);
        }
        if (! $response->successful()) {
            return response()->json(['message' => 'Falha ao consultar o serviço externo.'], 502);
        }
        $data = $response->json();
        if (! is_array($data) || empty($data['cnpj']) || preg_replace('/\D/', '', (string) $data['cnpj']) !== $cnpj) {
            return response()->json(['message' => 'Resposta inválida do serviço externo.'], 502);
        }

        try {
            $now = now();
            DB::table('companies')->upsert([[
                'cnpj' => $cnpj,
                'razao_social' => $data['razao_social'] ?? null,
                'nome_fantasia' => $data['nome_fantasia'] ?? null,
                'uf' => $data['uf'] ?? null,
                'municipio' => $data['municipio'] ?? null,
                'situacao_cadastral' => $data['descricao_situacao_cadastral'] ?? $data['situacao_cadastral'] ?? null,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]], ['cnpj'], ['razao_social', 'nome_fantasia', 'uf', 'municipio', 'situacao_cadastral', 'data', 'updated_at']);
            return response()->json(DB::table('companies')->where('cnpj', $cnpj)->first());
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Não foi possível persistir o cadastro.'], 500);
        }
    })->where('cnpj', '[0-9.\/-]+');

    // GET /api/v1/empresas?uf=MT&municipio=Cuiaba&situacao_cadastral=Ativa&per_page=20
    Route::get('/empresas', function (Request $request) {
        try {
            $filters = validator($request->query(), [
                'uf' => ['sometimes', 'string', 'size:2'],
                'municipio' => ['sometimes', 'string', 'max:150'],
                'situacao_cadastral' => ['sometimes', 'string', 'max:100'],
                'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            ])->validate();
            $query = DB::table('companies')->select([
                'id', 'cnpj', 'razao_social', 'nome_fantasia', 'uf', 'municipio', 'situacao_cadastral', 'created_at', 'updated_at',
            ]);
            foreach (['uf', 'municipio', 'situacao_cadastral'] as $field) {
                if (isset($filters[$field])) {
                    $query->where($field, $field === 'uf' ? strtoupper($filters[$field]) : $filters[$field]);
                }
            }
            return response()->json($query->orderBy('id')->paginate((int) ($filters['per_page'] ?? 15)));
        } catch (ValidationException $exception) {
            return response()->json(['message' => 'Parâmetros inválidos.', 'errors' => $exception->errors()], 422);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Não foi possível listar as empresas.'], 500);
        }
    });

    // GET /api/v1/empresas/cnpj/{cnpj}
    Route::get('/empresas/cnpj/{cnpj}', function (string $cnpj) use ($validCnpj) {
        $cnpj = preg_replace('/\D/', '', $cnpj);
        if (! $validCnpj($cnpj)) {
            return response()->json(['message' => 'CNPJ inválido.'], 422);
        }
        try {
            $company = DB::table('companies')->where('cnpj', $cnpj)->first();
            if (! $company) {
                return response()->json(['message' => 'Empresa não encontrada.'], 404);
            }
            $company->data = json_decode($company->data, true);
            return response()->json($company);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Não foi possível consultar a empresa.'], 500);
        }
    })->where('cnpj', '[0-9.\/-]+');

    // GET /api/v1/empresas/{id}
    Route::get('/empresas/{id}', function (string $id) {
        try {
            $company = DB::table('companies')->where('id', $id)->first();
            if (! $company) {
                return response()->json(['message' => 'Empresa não encontrada.'], 404);
            }
            $company->data = json_decode($company->data, true);
            return response()->json($company);
        } catch (\Throwable $exception) {
            report($exception);
            return response()->json(['message' => 'Não foi possível consultar a empresa.'], 500);
        }
    })->where('id', '[0-9]+');
});