<?php

namespace App\Http\Controllers;

use App\Support\License;
use Illuminate\Http\Request;

/** POST /license/push {p, g}: the license panel tells this site its new state (renewed, expired, suspended). */
class LicensePushController extends Controller
{
    public function __invoke(Request $request)
    {
        $error = License::acceptPush((string) $request->input('p'), (string) $request->input('g'));

        return $error
            ? response()->json(['ok' => false, 'error' => $error], 422)
            : response()->json(['ok' => true]);
    }
}
