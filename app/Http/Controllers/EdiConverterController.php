<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\MyFunctions\PurchaseOrder;
use App\MyFunctions\Edi850Converter;

class EdiConverterController extends Controller
{

/******************************************************************
Based on the instructions for this demo
create a new class that will represent the purchase order model
Load the purchase order class with test data.

Convert that data into edi 850 format and display in the browser
******************************************************************/

	public function index() 
	{
		$purchaseOrder = new PurchaseOrder();
		$PODataArray = $purchaseOrder->getData();

		// Convert the data into edi format
		$ediConverter = new Edi850Converter($PODataArray);
		$ediData = $ediConverter->convert();

		return view('ediViewer')->with('ediData',$ediData);
	}

}