<?php
$rawhtmlAWeber = file_get_contents( 'rawhtmlform_aweber.txt' );
$rawhtmlMailChimp = file_get_contents( 'rawhtmlform_mailchimp.txt' );
$rawhtmlGetResponse = file_get_contents( 'rawhtmlform_getresponse.txt' );
$rawhtmlActiveCampaign = file_get_contents( 'rawhtmlform_activecampaign.txt' );
$rawhtmlMadMimi = file_get_contents( 'rawhtmlform_madmimi.txt' );

$document = new DOMDocument();
libxml_use_internal_errors(true);
$document->loadHTML( $rawhtmlAWeber );
libxml_clear_errors();
$form = $document->getElementsByTagName( 'form' );
$input = $document->getElementsByTagName( 'input' );

$optinParams = array();

if ( $form->length > 0 ) {
  for ( $i=0; $i<$form->length; $i++ ) {
    $formTag = $form->item( $i );

    $action = $formTag->attributes->getNamedItem( 'action' )->value;
    $method = $formTag->attributes->getNamedItem( 'method' )->value;
    $optinParams['action'] = $action;
    $optinParams['method'] = $method;
  }
}

if ( $input->length > 0 ) {
  for ( $i=0; $i<$input->length; $i++ ) {
    $inputTag = $input->item( $i );

    $nameAttr = $inputTag->attributes->getNamedItem( 'name' );
    if ( !is_null( $nameAttr ) ) {
    	$name = $nameAttr->value;
    } else {
    	$name = '';
    }

    $type = $inputTag->attributes->getNamedItem( 'type' )->value;

    if ( $type == 'hidden' || $type == 'submit' ) {
    	$hiddenValue = $inputTag->attributes->getNamedItem( 'value' )->value;
    	$optinParams['hiddenfields'][] = array( 'name' => $name, 'value' => $hiddenValue );
    } else {
    	$optinParams['fields'][] = $name;
    }
  }
}

$optinFields = array();
foreach ( $optinParams['fields'] as $field ) {
	$lowerCaseField = strtolower( $field );

	if ( strpos( $lowerCaseField, 'email' ) !== false ) {
		$optinFields['fields']['email'][$field] = 'mtasuandi@outlook.com';
	}

	if ( $lowerCaseField != 'fullname' ) {
		if ( strpos( $lowerCaseField, 'fname' ) !== false || strpos( $lowerCaseField, 'firstname' ) !== false ) {
			$optinFields['fields']['name'][$field] = 'First Name';
		}

		if ( strpos( $lowerCaseField, 'lname' ) !== false || strpos( $lowerCaseField, 'lastname' ) !== false ) {
			$optinFields['fields']['name'][$field] = 'Last Name';
		}
	}

	if ( strpos( $lowerCaseField, 'name' ) !== false || strpos( $lowerCaseField, 'fullname' ) !== false ) {
		$optinFields['fields']['name'][$field] = 'Full Name';
	}

	if ( strpos( $lowerCaseField, 'phone' ) !== false ) {
		$optinFields['fields']['phone'][$field] = '+6281220504072';
	}

	if( preg_match( '([a-zA-Z].*[0-9]|[0-9].*[a-zA-Z])', $lowerCaseField ) ) {
		$optinFields['fields']['unique'][$field] = '';
	}
}

foreach ( $optinParams['hiddenfields'] as $hiddenfield ) {
	$optinFields['fields']['hidden'][$hiddenfield['name']] = $hiddenfield['value'];
}

$emailField = array();
if ( isset( $optinFields['fields']['email'] ) ) {
	$emailField = $optinFields['fields']['email'];
}
$nameField = array();
if ( isset( $optinFields['fields']['name'] ) ) {
	$nameField = $optinFields['fields']['name'];
}
$phoneField = array();
if ( isset( $optinFields['fields']['phone'] ) ) {
	$phoneField = $optinFields['fields']['phone'];
}
$uniqueField = array();
if ( isset( $optinFields['fields']['unique'] ) ) {
	$uniqueField = $optinFields['fields']['unique'];
}
$hiddenField = array();
if ( isset( $optinFields['fields']['hidden'] ) ) {
	$hiddenField = $optinFields['fields']['hidden'];
}

$optinSendParam = array_merge(
	$emailField,
	$nameField,
	$phoneField,
	$uniqueField,
	$hiddenField
);

// echo "<pre>"; print_r( $optinSendParam ); echo "</pre>"; exit();

$postData = '';
foreach ( $optinSendParam as $k => $v ) {
	$postData .= $k . '='.$v.'&'; 
}
$postData = rtrim( $postData, '&' );

$ch = curl_init();
$url = $optinParams['action'];

$urlPost = $url;
if ( strpos( $url, 'http:' ) === false && strpos( $url, 'https:' ) === false ) {
	$urlPost = 'https:' . $url;
}

curl_setopt( $ch, CURLOPT_URL, $urlPost );
curl_setopt( $ch, CURLOPT_POST, true );
curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

$output = curl_exec( $ch );

curl_close( $ch );
var_dump( $output );
