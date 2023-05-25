var LAST_CARD_USED = '';
var LAST_CARD_VALUE = '';

var LAST_EXPDATE_VALUE = '';

jQuery(document).ready(function(){

	///// CREDIT CARD /////
	// restrict to accept only numbers and arrows
	const cardnumberInput = jQuery('input[name="cardnumber"]');

	cardnumberInput.attr('onkeypress', 'return (event.charCode >= 48 && event.charCode <= 57) || (event.charCode >= 37 && event.charCode <= 40)');

	cardnumberInput.keyup(function(e){
		handleCreditCardInput(this, e);
	});

	if( cardnumberInput.val().length ) {
		cardnumberInput.trigger('keyup');
	}

	///// EXPIRY DATE /////
	// restrict to accept only numbers, arrows and slash
	const expdateInput = jQuery('input[name="expdate"]');

	expdateInput.attr('onkeypress', "return (event.charCode >= 48 && event.charCode <= 57) || (event.charCode >= 37 && event.charCode <= 40) || (event.charCode == 47 && this.value.length > 0 && this.value.indexOf('/') == -1)");

	expdateInput.keyup(function(e){
		handleExpiryDateInput(this, e);
	});

	if( expdateInput.val().length ) {
		expdateInput.trigger('keyup');
	}

	///// CVC /////
	// restrict to accept only numbers and arrows
	jQuery('input[name="cvc"]').attr('onkeypress', 'return (event.charCode >= 48 && event.charCode <= 57) || (event.charCode >= 37 && event.charCode <= 40)');

	// remove required-field effect on focus

	jQuery('.offcc-payment-field .offcc-payment-field-wrapper input[type="text"]').on('focus', function(){
		jQuery(this).parent().removeClass('field-required');
	});

});

function handleCreditCardInput(input, event) {

	var val = jQuery(input).val();

	if( event.keyCode < 48 || event.keyCode > 57 ) {
		if( LAST_CARD_VALUE.length == val.length || LAST_CARD_VALUE.trim() == val.trim() ) {
			LAST_CARD_VALUE = val;
			return false;
		}
	}

	LAST_CARD_VALUE = val;

	const card = new CreditCard(val);
	
	var card_type 	= card.getCardType();
	var cc_text 	= card.formatCreditCard(card_type);

	jQuery(input).val(cc_text);
	
	if( LAST_CARD_USED != card_type ) {

		if( LAST_CARD_USED.length > 0 ) {
			jQuery('#credit-card-brand').removeClass(LAST_CARD_USED);
		}

		if( card_type.length > 0 ) {
			jQuery('#credit-card-brand').addClass(card_type);

			// change max length
			jQuery(input).attr('maxlength', (
				CreditCard.properties[card_type]['size']+
				CreditCard.properties[card_type]['blank'].length
			));
		} else {
			// no valid card > set max length to 16
			jQuery(input).attr('maxlength', 16);
		}

		LAST_CARD_USED = card_type;

	}

}

function handleExpiryDateInput(input, event) {
	
	var val = jQuery(input).val();

	if( (event.keyCode < 48 || event.keyCode > 57) && event.keyCode != 191 ) {
		if( LAST_EXPDATE_VALUE.length == val.length ) {
			LAST_EXPDATE_VALUE = val;
			return false;
		}
	}

	LAST_EXPDATE_VALUE = val;

	if( val.length == 0 ) {
		return false;
	}

	var parts = val.split('/');
	if( parts.length == 1 && parts[0].length > 2 ) {
		var app = parts[0];
		parts[0] = app.substr(0, 2);
		parts[1] = app.substr(2);
	}

	var month 	= parts[0].trim();
	var year 	= (parts.length > 1 ? parts[1].trim() : '');
	
	if( month.length == 0 ) {
		month = '';
	} else if( month.length == 1 ) {
		if( parseInt(month) > 1 ) {
			month = '0'+month;
		} else if( year.length ) {
			month = '01';
		}
	} else if( month.length == 2 ) {
		if( parseInt(month) > 12 ) {
			year = (parseInt(month)-10);
			month = '01';
		}
	}

	val = month+(month.length == 2 || year.length > 0 ? ' / '+year : '');
	jQuery(input).val(val);

	LAST_EXPDATE_VALUE = val;

}

function validateCreditCardForm() {
	var ok = true;

	////////////////////////////////
	// cardholder name validation //
	////////////////////////////////
	const cardholderInput = jQuery('input[name="cardholder"]');
	const cardholder = cardholderInput.val().trim();
	if( cardholder.length <= 2 || cardholder.indexOf(' ') == -1 ) {
		ok = false;

		cardholderInput.parent().addClass('field-required');
	} else {
		cardholderInput.parent().removeClass('field-required');
	}

	///////////////////////////////////
	// credit card number validation //
	///////////////////////////////////
	const cardnumberInput = jQuery('input[name="cardnumber"]');
	const cardnumber = cardnumberInput.val().trim().replace(/\D/g,'');

	const card = new CreditCard(cardnumber);
	if( !card.isValid() ) {
		ok = false;

		cardnumberInput.parent().addClass('field-required');
	} else {
		cardnumberInput.parent().removeClass('field-required');
	}

	////////////////////////////
	// expiry date validation //
	////////////////////////////
	const expdateInput = jQuery('input[name="expdate"]');
	const month_year = expdateInput.val().split('/');

	const now = new Date();

	// trim month and replace all non-digit chars, then cast it to INT
	const month = parseInt(month_year[0].trim().replace(/\D/g,''));
	// trim year and replace all non-digit chars, 20 year suffix and cast it to INT
	const year 	= parseInt((''+now.getFullYear()).substr(0, 2)+(month_year.length > 1 ? month_year[1].trim().replace(/\D/g,'') : ''));

	if( month < 1 || month > 12 || year < now.getFullYear() || ( year == now.getFullYear() && month < (now.getMonth()+1) ) ) {
		ok = false;

		expdateInput.parent().addClass('field-required');
	} else {
		expdateInput.parent().removeClass('field-required');
	}

	////////////////////////////////
	// credit card CVC validation //
	////////////////////////////////
	const cvcInput = jQuery('input[name="cvc"]');
	const cvc = cvcInput.val().trim().replace(/\D/g,'');

	if( cvc.length < 3 ) {
		ok = false;

		cvcInput.parent().addClass('field-required');
	} else {
		cvcInput.parent().removeClass('field-required');
	}

	return ok;
}

/* Credit Card class */

function CreditCard(card) {
	this.set(card);
}

CreditCard.prototype.set = function(card) {
	this.card = [];
	for( var i = 0; i < card.length; i++ ) {
		var ch = card.charCodeAt(i);
		if( ch >= 48 && ch <= 57 ) {
			this.card.push(ch-48);
		}
	}
}

CreditCard.prototype.get = function() {
	return this.card;
}

CreditCard.prototype.isEnoughSpace = function(len) {
	return ( this.card.length >= len );
}

CreditCard.prototype.isEmpty = function() {
	return !this.isEnoughSpace(1);
}

CreditCard.prototype.isValid = function() {
	const type = this.getCardType();

	if( type.length && this.card.length == CreditCard.properties[type].size ) {
		return true;
	}

	return false;
}

CreditCard.prototype.getNumberToIndex = function(i) {
	var n = 0;
	var factor = 1;
	for( i = i-1 ; i >= 0; i-- ) {
		n += factor*this.card[i];
		factor *= 10;
	}
	return n;
}

CreditCard.prototype.isVisa = function() {
	return this.matchBrandRanges([
			[4]
		]);
}

CreditCard.prototype.isMasterCard = function() {
	return this.matchBrandRanges([
			[51, 55],
			[2221, 2720]
		]);
}

CreditCard.prototype.isAmericanExpress = function() {
	return this.matchBrandRanges([
			[34],
			[37]
		]);
}

CreditCard.prototype.isDiners = function() {
	return this.matchBrandRanges([
			[300, 305],
			[36],
			[38, 39]
		]);
}

CreditCard.prototype.isDiscover = function() {
	return this.matchBrandRanges([
			[6011],
			[65],
			[622126, 622925],
			[644, 649]
		]);
}

CreditCard.prototype.isJCB = function() {
	return this.matchBrandRanges([
			[3528, 3589]
		]);
}

CreditCard.prototype.getCardType = function() {
	if( this.isVisa() ) {
		return CreditCard.VISA;
	} else if( this.isMasterCard() ) {
		return CreditCard.MASTERCARD;
	} else if( this.isAmericanExpress() ) {
		return CreditCard.AMERICAN_EXPRESS;
	} else if( this.isDiners() ) {
		return CreditCard.DINERS;
	} else if( this.isDiscover() ) {
		return CreditCard.DISCOVER;
	} else if( this.isJCB() ) {
		return CreditCard.JCB;
	}

	return '';
}

CreditCard.prototype.matchBrandRanges = function(ranges) {

	for( var i = 0; i < ranges.length; i++ ) {
		var r = ranges[i];

		if( r.length == 1 ) {

			if( this.isEnoughSpace((''+r[0]).length) && this.getNumberToIndex((''+r[0]).length) == r[0] ) {
				return true;
			} 

		} else if( r.length == 2 ) {

			var len = Math.max( (''+r[0]).length, (''+r[1]).length );

			if( this.isEnoughSpace(len) ) {

				var val = this.getNumberToIndex(len);

				if( r[0] <= val && val <= r[1] ) {
					return true;
				}

			}

		}

	}

	return false;
}

CreditCard.prototype.formatCreditCard = function(card_type) {
	if( card_type === undefined ) {
		card_type = this.getCardType();
	}

	var blank_spaces = [];
	if( card_type.length  > 0 ) {
		blank_spaces = CreditCard.properties[card_type]['blank'];
	}

	var cc_str = '';
	for( var i = 0; i < this.card.length; i++ ) {
		cc_str += this.card[i];
		if( blank_spaces.indexOf(i+1) != -1 ) {
			cc_str += ' ';
		}
	}

	return cc_str;
}

CreditCard.properties = {
	'visa':{
		'size':16,
		'blank':[4, 8, 12]
	},
	'mastercard':{
		'size':16,
		'blank':[4, 8, 12]
	},
	'amex':{
		'size':15,
		'blank':[4, 10]
	},
	'discover':{
		'size':16,
		'blank':[4, 8, 12]
	},
	'diners':{
		'size':14,
		'blank':[4, 8, 12]
	},
	'jcb':{
		'size':16,
		'blank':[4, 8, 12]
	},
};

CreditCard.VISA = 'visa';
CreditCard.MASTERCARD = 'mastercard';
CreditCard.AMERICAN_EXPRESS = 'amex';
CreditCard.DINERS = 'diners';
CreditCard.DISCOVER = 'discover';
CreditCard.JCB = 'jcb';