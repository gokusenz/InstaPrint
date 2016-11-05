<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use App\Libraries\Instagram;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use App\Models\Token;

/**
 * Description of OperationController
 *
 * @author keittirat.sat
 */
class OperationController extends Controller {

	private $instagram;

	public function __construct() {
		$config			 = config('instagram');
		$this->instagram = new Instagram($config);
	}

	public function getIndex() {
		return ['url' => $this->instagram->getLoginUrl(array('basic', 'public_content'))];
	}

	public function getCallback() {
		$code	 = Input::get('code');
		$resp	 = $this->instagram->getAccessToken($code);

		if ($resp == false) {
			return redirect()->action('AdminController@getToken')->with('status', 'token_error');
		}

		Log::info($resp);

		$token				 = new Token;
		$token->name		 = $resp['user']['full_name'];
		$token->token		 = $resp['access_token'];
		$token->is_active	 = true;
		$token->picture		 = $resp['user']['profile_picture'];
		$token->save();

		return redirect()->action('AdminController@getToken')->with('status', 'token_add_success');
	}

	public function getDisbleToken() {
		$token_id = Input::get('id');
		$token = Token::find($token_id);
		$token->is_active = false;
		$token->save();
		return redirect()->action('AdminController@getToken')->with('status', 'token_disabled_success');
	}
}
