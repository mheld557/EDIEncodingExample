<?php

namespace App\MyFunctions;
use Carbon\Carbon;

class Edi850Converter
{
	private $poData;
	private $ediArray;
	

	function __construct(array $PurchaseOrder)
	{
		$this->poData = $PurchaseOrder;
	}

	function convert()
	{
		$this->ediArray = array();

		// build the full 850 message by calling all of the segments
		
		// 850 headers
		$this->buildISASegment();
		$this->buildGSSegment();

		// 850 body conforming to the auto customs spec
		$this->buildSTSegment();
		$this->buildBEGSegment();
		$this->buildCURSegment();
		$this->buildREFSegment();
		$this->buildSACSegment();
		$this->buildITDSegment();
		$this->buildDTMSegment();
		$this->buildTD5Segment();
		$this->buildN9Segment();
		$this->buildN1Segment();
		$this->buildPO1Segment();
		$this->buildCTTSegment();
		$this->buildSESegment();

		// 850 trailers
		$this->buildGESegment();
		$this->buildIEASegment();

		return $this->ediArray;
	}

	function buildISASegment()
	{
		// build the ISA segement
		// ISA 01 = 00
		// ISA 02 = 10 character blank
		// ISA 03 00
		// ISA 04 10 character blank
		// ISA 05 'ZZ'
		// ISA 06 Sender's Interchange ID (char 15)
		// ISA 07 receiver's ID, use the receiver telephone so id = '12'
		// ISA 08 receiver's phone. (char 15)
		// ISA 09 Message Date (yymmdd) 
		// ISA 10 Message Time (hhmm)
		// ISA 11 Control Standards ID (always a 'U')
		// ISA 12 Standard Version Number  '05010'
		// ISA 13 Interchange Control Number (PurchaseOrderControlId) 9 digits, padded to the left
		// ISA 14 Acknowledgment Request - '0'
		// ISA 15 Usage Indicator, P = production.
		// ISA 16 delimiters 

		$messageDate = Carbon::now()->format('ymd');
		$messageTime = Carbon::now()->format('hi');
		$senderId = str_pad($this->poData['SendersId'], 15);
		$receiverPhone = str_pad($this->poData['ShippingPhone'], 15);
		$controlId = str_pad($this->poData['PurchaseOrderControlId'], 9, '0', STR_PAD_LEFT);

		$isaString = 'ISA*00*          *00*          *ZZ*';
		$isaString .= $senderId;
		$isaString .= '*12*';
		$isaString .= $receiverPhone;
		$isaString .= '*'.$messageDate.'*'.$messageTime;
		$isaString .= '*U*05010*'.$controlId.'*0*P*> ';

		$this->ediArray[] = $isaString;
		return;
	}

	function buildGSSegment()
	{

		// GS-01 functional identifier 'PO' for purchase orders
		// GS-02 Sender Identifier (vchar 2-15)
		// GS-03 Reciever's code (vchar 2-15)
		// GS-04 Date (YYYYMMDD)
		// GS-05 Time (hhmm)
		// GS-06 Group Control Number (number 1 - 9) '1'
		// GS-07 Responsible Agency Code. 'X'
		// GS-08 Version (vchar 1-12) '005010'

		$messageDate = Carbon::now()->format('Ymd');
		$messageTime = Carbon::now()->format('hi');

		$gsString = "GS*PO*".$this->poData['SendersId']."*".$this->poData['ShippingPhone'];
		$gsString .= "*".$messageDate.'*'.$messageTime;
		$gsString .= "*1*X*005010";

		$this->ediArray[] = $gsString;
		return;
	}

	function buildSTSegment()
	{
		// ST-01 Identifier Code  '850 as provided in spec'
		// ST-02 Control Number

		$stString = "ST*850*";
		$stString .= $this->poData['PurchaseOrderControlId'];

		$this->ediArray[] = $stString;
		return;
	}

	function buildBEGSegment()
	{

		// BEG-01 Purpose Code - '00' Original defined in spec.
		// BEG-02 Type Code - 'DS' defined in spec
		// BEG-03 Purchase Order Number (number 1 - 22)
		// BEG-04 NA
		// BEG-05 Purchase order date, not message date

		$begString = "BEG*00*DS*";
		$begString .= $this->poData['PurchaseOrderNumber'];
		$begString .= "**";
		$begString .= $this->poData['PurchaseOrderDate'];

		$this->ediArray[] = $begString;
		return;
	}

	function buildCURSegment()
	{
		// CUR-01 Identifier Code  'BY', defined in spec
		// CUR-02 Currency Code (char 3)

		$curString = "CUR*BY*";
		$curString .= $this->poData['PurchaseOrderCurrencyCode'];

		$this->ediArray[] = $curString;
		return;
	}

	function buildREFSegment()
	{

		// REF-01 Identifier Qualifier
		// 'CO'  Customer Order Number  (our control id)
		// 'GK'  Third Party Reference Number, (Our PurchaseOrderNumber) 
		// 'IA'  Internal Vendor Number
		// REF-02 Refernce Id, (matching REF-01) (vchar 1 -50)

		// One per line

		$this->ediArray[] = "REF*CO*".$this->poData['PurchaseOrderControlId'];
		$this->ediArray[] = "REF*GK*".$this->poData['PurchaseOrderNumber'];
		$this->ediArray[] = "REF*IA*".$this->poData['InternalVendorNumber'];
		return;
	}

	function buildSACSegment()
	{
		// SAC-01  Allowance or Charge Indicator  'N' as per spec
		// SAC-02  Service, Promotion, Allowance or Charge Code 
		//   B800 Credit
		//   C310 Discount
		//   G830 Shipping and Handling
		//   H850 Tax
		// SAC-03 N/A
		// SAC-04 N/A
		// SAC-05  Amount  (based on sac-02)
		// SAC-15 Description (Optional) (vchar 1 - 80)
		// Need to pad 06 - 15 with '*'

		//PurchaseOrderCredit
		$this->ediArray[] = "SAC*N*B800***".$this->poData['PurchaseOrderCredit']."**********";

		//PurchaseOrderDiscount
		$this->ediArray[] = "SAC*N*C310***".$this->poData['PurchaseOrderDiscount']."**********";

		//PurchaseOrderShippingHandling
		$this->ediArray[] = "SAC*N*G830***".$this->poData['PurchaseOrderShippingHandling']."**********";
		
		//PurchaseOrderTax
		$this->ediArray[] = "SAC*N*H850***".$this->poData['PurchaseOrderTax']."**********";

		return;
	}

	function buildITDSegment()
	{
		// ITD-01 Terms Type Code '14' as per spec
		// ITD-02 Terms Basis Date Code, '3' Invoice Date, as per spec
		// ITD-03 Terms Discount Percent 
		// ITD-05 Terms Discount Days Due
		// ITD-07 Terms Net Days
		// ITD-12 Desciption
		// Only using ITD-01 and ITD-02. 
		// Pad with '*' for ITD-03 to ITD-12.

		$this->ediArray[] = "ITD*14*3**********";
		return;
	}

	function buildDTMSegment()
	{
		// DTM-01 Date/Time Qualifier , '038' Ship No Later, as per spec
		// DTM-02 Date (yyyymmdd)

		$this->ediArray[] = "DTM*038*".$this->poData['ShippingDateDueBy'];
		return;
	}

	function buildTD5Segment()
	{
		// TD5-04 transportation method/type code
		//  A - air
		//  M - Motor (common carrier)
		//  U - Private Parcel
		// TD5-05 Routing Carrier Description
		// TD5-12 Serivce Level Code
		//  ON - Overnight, next day
		//  P1 - Priority Service, Expedited, 1 -3 business days
		//  SE - Second Day, 2 business days
		//  ST - Standard Class, 3 -5 business days
		// TD5-13 Service Level Code
		//  DS - Door Service, Home Delivery
		//  ET - Proof of Delivery (POD) with signature
		//  PX - Premium Service, white glove
		//  SD - Saturday

		// TD5-04
		 switch (strtolower($this->poData['ShippingMethod']))
		 {
		 	case 'air': 
		 		$method = 'A';
		 		break;
		 	case 'motor':
		 		$method = 'M';
		 		break;
		 	case 'private':
		 		$method = 'U';
		 }

		 // TD5-12 
		switch (strtolower($this->poData['ShippingServiceTime']))
		 {
		 	case 'next day': 
		 		$TD512 = 'ON';
		 		break;
		 	case 'priority':
		 		$TD512 = 'P1';
		 		break;
		 	case 'second day':
		 		$TD512 = 'SE';
		 		break;
		 	case 'standard':
		 		$TD512 = 'ST';
		 		break;
		 }

		 //TD5-13
		switch (strtolower($this->poData['ShippingServiceDelivery']))
		 {
		 	case 'home delivery': 
		 		$TD513 = 'DS';
		 		break;
		 	case 'signature required':
		 		$TD513 = 'ET';
		 		break;
		 	case 'premium service':
		 		$TD513 = 'PX';
		 		break;
		 	case 'saturday':
		 		$TD513 = 'SD';
		 		break;
		 }

		 $this->ediArray[] = 'TD5****'.$method.'*'.$this->poData['ShippingCarrier'].'*******'.$TD512.'*'.$TD513;
		 return;
	}

	function buildN9Segment()
	{
		// N9-01 Reference Identifier qualifier, 'L1' Letters or Notes, from spec.
		// N9-03 Free Form Description. (vchar 1 -45)
		//    PACKING SLIP MESSAGE
		//    Gift Message
		// Next segment contains the real message text
		// MTX-02 Text Data

		// check if we have any shipping messages
		if (array_key_exists('ShippingMessage', $this->poData)) {
			if (!empty($this->poData['ShippingMessage'])) {
				$this->ediArray[] = 'N9*L1**'.$this->poData['ShippingMessageType'];
				$this->ediArray[] = 'MTX**'.$this->poData['ShippingMessage'];
			}
		}

		return;
	}

	function buildN1Segment()
	{
		// This segment is made up of 1 to 4 sections of 
		// N1, N2, N3,N4, Per
		// N1 Party Identification
		// N2 Additional Name Info
		// N3 Party Location
		// N4 Geo Location
		// PER Admin contact

		// N1-01 Entity Identifier Code
		//  BT - Bill-to-party
		//  SO - Sold to, Consumer bill to address
		//  ST - Ship to
		//  VN - Vendor
		// N1-02 Name, (vchar 1 - 60)
		
		// N2-01 Additional Name (vchar 1 - 60)
		
		// N3-01 Address (vchar 1-55)
		// N3-02 Address Info (vchar 1-55), Optional
		
		// N4-01 City Name (vchar 2-30)
		// N4-02 State or Providence (char 2)
		// N4-03 Postal Code (vchar 3-15)
		// N4-04 Country Code (vchar 2-3)

		// PER-01 Contact Function Code 'IC' per spec.
		// PER-02 Name (vchar 1-60)
		// PER-03 Communication Number Qualifier, 'TE' Telephone. per spec.
		// PER-04 Number  (vchar 1 - 256)
		// PER-05 Number Qualifier, 'EM' email per spec.
		// PER-06 email, (vchar 1 - 256)

		// build each segment if we have the information

		if (array_key_exists('BillToFirstName', $this->poData)) {
			if (!empty($this->poData['BillToFirstName'])) {
				// Build the bill to lines

				$this->ediArray[] = 'N1*BT*'.$this->poData['BillToFirstName']." ".$this->poData['BillToLastName'];
				$this->ediArray[] = 'N3*'.$this->poData['BillToAddressLine1']."*".$this->poData['BillToAddressLine2'];

				$n4Str = 'N4*'.$this->poData['BillToCity'].'*'.$this->poData['BillToState'].'*';
				$n4Str .= $this->poData['BillToPostalCode'].'*'.$this->poData['BillToCountry'];
				$this->ediArray[] = $n4Str;

				$perStr = 'PER*IC*'.$this->poData['BillToFirstName']." ".$this->poData['BillToLastName'];
				$perStr .= '*TE*'.$this->poData['BillToPhone'].'*EM*'.$this->poData['BillToEmail'];
		 		$this->ediArray[] = $perStr;
			}
		}

		if (array_key_exists('CustomerFirstName', $this->poData)) {
			if (!empty($this->poData['CustomerFirstName'])) {
				// Build the sold to line

				$this->ediArray[] = 'N1*SO*'.$this->poData['CustomerFirstName']." ".$this->poData['CustomerLastName'];
				$this->ediArray[] = 'N3*'.$this->poData['CustomerAddressLine1']."*".$this->poData['CustomerAddressLine2'];

				$customerStr = 'N4*'.$this->poData['CustomerCity'].'*'.$this->poData['CustomerState'].'*';
				$customerStr .= $this->poData['CustomerPostalCode'].'*'.$this->poData['CustomerCountry'];
				$this->ediArray[] = $customerStr;

				$customerContactStr = 'PER*IC*'.$this->poData['CustomerFirstName']." ".$this->poData['CustomerLastName'];
				$customerContactStr .= '*TE*'.$this->poData['CustomerPhone'].'*EM*'.$this->poData['CustomerEmail'];
		 		$this->ediArray[] = $customerContactStr;
			}
		}

		if (array_key_exists('ShippingAddressLine1', $this->poData)) {
			if (!empty($this->poData['ShippingAddressLine1'])) {
				// Build the ship to line
				
				$this->ediArray[] = 'N1*ST*'.$this->poData['ShippingFirstName']." ".$this->poData['ShippingLastName'];
				$this->ediArray[] = 'N3*'.$this->poData['ShippingAddressLine1']."*".$this->poData['ShippingAddressLine2'];

				$shippingStr = 'N4*'.$this->poData['ShippingCity'].'*'.$this->poData['ShippingState'].'*';
				$shippingStr .= $this->poData['ShippingPostalCode'].'*'.$this->poData['ShippingCountry'];
				$this->ediArray[] = $shippingStr;

				$shippingContactStr = 'PER*IC*'.$this->poData['ShippingFirstName']." ".$this->poData['ShippingLastName'];
				$shippingContactStr .= '*TE*'.$this->poData['ShippingPhone'].'*EM*'.$this->poData['ShippingEmail'];
		 		$this->ediArray[] = $shippingContactStr;
			}
		}

		if (array_key_exists('VendorName', $this->poData)) {
			if (!empty($this->poData['VendorName'])) {
				// Build the vendor line

				$this->ediArray[] = 'N1*VN*'.$this->poData['VendorName'];
				$this->ediArray[] = 'N3*'.$this->poData['VendorAddressLine1']."*".$this->poData['VendorAddressLine2'];

				$vendorStr = 'N4*'.$this->poData['VendorCity'].'*'.$this->poData['VendorState'].'*';
				$vendorStr .= $this->poData['VendorPostalCode'].'*'.$this->poData['VendorCountry'];
				$this->ediArray[] = $vendorStr;

				$vendorContactStr = 'PER*IC*'.$this->poData['VendorName'];
				$vendorContactStr .= '*TE*'.$this->poData['VendorPhone'].'*EM*'.$this->poData['VendorEmail'];
		 		$this->ediArray[] = $vendorContactStr;
			}
		}

		return;
	}

	function buildPO1Segment()
	{
		// Baseline Item Data made up of PO1 and PID segments
		// PO1-01 Assigned Id (vchar 1 - 20)
		// PO1-02 Quantity  (number 1 - 15)
		// PO1-03 Unit or Basis 'EA' per spec
		// PO1-04 Unit Price, (number 1 - 17)
		// PO1-06 Product/Service ID Qualifier, 'IN' per spec
		// PO1-07 Product/Service Id (vchar 1 - 48)
		// PO1-08 Product/Service Id Qualifier 'VN' Vendor's item number, per spec
		// PO1-09 Product/Service Id Description (vchar 1 - 48)
		// PO1-10 Code 'UP' per spec
		// PO1-11 upc number.

		// PID-01 Item Description type 'F' freeform per spec.
		// PID-02 Product code '08' product per spec.
		// PID-05 Description, (vchar 1 - 80)

		// build the order item lines
		// building two lines the PO1 and the PID for each line item
		foreach ($this->poData['PurchaseOrderItems'] as $orderItem) {

			$po1Str = 'PO1*'.$orderItem['id'].'*'.$orderItem['quantity'].'*EA*';
			$po1Str .= $orderItem['price'].'**IN*'.$orderItem['itemNumber'];
			$po1Str .= '*VN*'.$orderItem['itemNumber'].'*UP*';
			$this->ediArray[] = $po1Str;

			// PID
			$this->ediArray[] = 'PID*F*08***'.$orderItem['description'];
		}

		return;
	}

	function buildCTTSegment()
	{
		// CTT-01 Number of Line Items
		$this->ediArray[] = 'CTT*'.$this->poData['PurchaseOrderNumberOfItems'];
		return;
	}

	function buildSESegment()
	{
		// SE-01 Number of included segments in the transaction set 
		// including ST and SE.
		// SE-02 Transaction set control number (vchar 4 -9)

		// so get the count of all lines from st to here.
		// Need to subtract the ISA and GS Headers, but also need to include the SE segement
		// that we are adding right now. So total count -1.

		$segmentCount = count($this->ediArray) - 1;
		$this->ediArray[] = 'SE*'.$segmentCount.'*'.$this->poData['PurchaseOrderControlId'];

		return;
	}

	function buildGESegment()
	{
		// GE-01 Number of transaction sets 
		// GE-01 Group Control Number

		$this->ediArray[] = 'GE*1*'.$this->poData['PurchaseOrderControlId'];
		return;
	}

	function buildIEASegment()
	{
		// IEA-01 Number of included functional groups, 1
		// IEA-02 Interchange Control Number. The same number as ISA13 

		$this->ediArray[] = 'IEA*1*'.$this->poData['PurchaseOrderControlId'];
		return;
	}

}