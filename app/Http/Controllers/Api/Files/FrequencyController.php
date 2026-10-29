<?php

namespace App\Http\Controllers\Api\Files;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Frequency;


class FrequencyController extends Controller
{
    public function index()
    {
        $data = Frequency::orderBy('id')
            ->get(['id', 'f_name'])
            ->map(fn ($f) => ['id' => $f->id, 'fName' => $f->f_name]);

        return response()->json($data);
    }
}
