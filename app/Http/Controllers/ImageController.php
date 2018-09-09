<?php

namespace App\Http\Controllers;

use App\User;
use App\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class ImageController extends Controller
{
	/**
	 * Vote a post
	 *
	 * @param \Illuminate\Http\Request, integer, integer
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function get(Request $request, $imageId)
	{
		$image = Image::find($imageId);
		if (!$image)
			return response()->json(['message' => "The request is incorrect."], 500);

		$imgPath = $image->path;
		ob_end_clean();
		return response()->download($imgPath);
	}
}