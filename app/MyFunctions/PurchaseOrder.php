<?php

namespace App\MyFunctions;

class PurchaseOrder
{

	// Since this is all sample data
	// I have set it directly, into one array that represents the full purchase order.
	// One method to load/set the data and one method to get the data.
	// instead of using seperate setter and getter methods for each element.

	private $PurchaseOrderData;

	function __construct() 
	{

		$this->PurchaseOrderData = array (
		 'CustomerFirstName' =>'Michael',
		 'CustomerLastName' =>'Held',
		 'CustomerAddressLine1' =>'557 Main Street',
		 'CustomerAddressLine2' =>'Apt 43',
		 'CustomerCity' =>'East Meadow',
		 'CustomerState' =>"NY",
		 'CustomerPostalCode' =>'11554',
		 'CustomerCountry' => 'US',
		 'CustomerPhone' =>'516-826-5883',
		 'CustomerEmail' =>'mheld557@gmail.com',

		 'BillToFirstName' =>'Michael',
		 'BillToLastName' =>'Held',
		 'BillToAddressLine1' =>'557 Main Street',
		 'BillToAddressLine2' =>'Apt 43',
		 'BillToCity' =>'East Meadow',
		 'BillToState' =>'NY',
		 'BillToPostalCode' =>'11554',
		 'BillToCountry' => 'US',
		 'BillToPhone' =>'561-826-5883',
		 'BillToEmail' =>'mheld557@gmail.com',
		 
		 'VendorName' =>'Auto Customs',
		 'VendorAddressLine1' =>'2303 SE 17th Street',
		 'VendorAddressLine2' =>'STE 102',
		 'VendorCity' =>'Ocala',
		 'VendorState' =>'FL',
		 'VendorPostalCode' =>'34471',
		 'VendorCountry' => 'US',
		 'VendorPhone' =>'877-204-7002',
		 'VendorEmail' =>'service@autocustoms.com',
		 'VendorContact' =>'John Smith',

		 'ShippingFirstName' =>'Michael',
		 'ShippingLastName' =>'Held',
		 'ShippingAddressLine1' =>'557 Marion Drive',
		 'ShippingAddressLine2' =>'Apt 43',
		 'ShippingCity' =>'East Meadow',
		 'ShippingState' =>'NY',
		 'ShippingPostalCode' =>'11554',
		 'ShippingCountry' => 'US',
		 'ShippingPhone' => '561-826-5883',
		 'ShippingEmail' =>'mheld557@gmail.com',
		 'ShippingMethod' =>'air',
		 'ShippingCarrier' =>'UPS',
		 'ShippingServiceTime' =>'next day',
		 'ShippingServiceDelivery' =>'signature required',
		 'ShippingDateDueBy' =>'20160615',
		 'ShippingMessage' =>'Order contains 3 boxes',
		 'ShippingMessageType' => 'Packing Slip Message',

		 'ReceiverId' =>'1234567',
		 'SendersId' => 'TruckXL',
		 'InternalVendorNumber' => '2584',
		 'PurchaseOrderControlId' => '1587',
		 'PurchaseOrderNumber' =>'3351',
		 'PurchaseOrderId' =>'101',
		 'PurchaseOrderDate' =>'20160613',
		 'PurchaseOrderDescription' =>'Front and rear bumper kits',
		 'PurchaseOrderNumberOfItems' =>'2',
		 'PurchaseOrderItems' => array(
			array (
				'id' => '1',
				'quantity' => '1',
				'price' => '200.00',
				'description' => 'Front bumper kit',
				'itemNumber' => '15550',
			),
			array (
				'id' => '2',
				'quantity' => '1',
				'price' => '260.00',
				'description' => 'Rear bumper kit',
				'itemNumber' => '35550',
			)
		 ),
		 'PurchaseOrderTotal' => '450',
		 'PurchaseOrderCurrencyCode' => 'USD',
		 'PurchaseOrderCredit' => '0',
		 'PurchaseOrderDiscount' => '0',
		 'PurchaseOrderShippingHandling' => '0',
		 'PurchaseOrderTax' => '0'
		);
	}

	function getData()
	{
		return $this->PurchaseOrderData;
	}
	
}

