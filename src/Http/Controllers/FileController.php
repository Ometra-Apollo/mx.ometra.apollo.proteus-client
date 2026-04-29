<?php

namespace Ometra\Apollo\Proteus\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Ometra\Apollo\Proteus\Events\FileDeleted;

class FileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate([
            'id' => ['required', 'string', 'max:40'],
            'name' => ['nullable', 'string', 'max:50'],
        ]);

        DB::table($this->tableName())->where('id', $data['id'])->delete();

        event(new FileDeleted(
            id: $data['id'],
            name: $data['name'] ?? null,
            payload: $request->all(),
        ));

        return response()->json(['status' => 'ok']);
    }

    private function tableName(): string
    {
        return config('proteus.table_prefix', '') . 'files_cache';
    }
}
