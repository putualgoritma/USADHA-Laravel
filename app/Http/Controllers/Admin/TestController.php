<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\TraitModel;
use Illuminate\Http\Request;

class TestController extends Controller
{
    use TraitModel;

    public function test(Request $request)
    {
        $dwn_arr = array();
        return $this->get_ref_plat($request->id);
    }
}
