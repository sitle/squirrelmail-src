<?php

	/*
	**  SquirrelMail 2
	**  Copyright (c) 1999-2001 The SquirrelMail Foundation
	**  Licensed under the GNU GPL.  For full terms see the file COPYING.
	**
	**  Utility Functions -- Security 
	**  $id$
	*/

	function sm_otp_encrypt($string, $epad) {
		$pad = base64_decode($epad);
		$encrypted = '';
		for ($i = 0; $i < strlen ($string); $i++) {
			 $encrypted .= chr (ord($string[$i]) ^ ord($pad[$i]));
		}
		return base64_encode($encrypted);
	}

	function sm_otp_decrypt($string, $epad) {
		$pad = base64_decode($epad);
		$encrypted = base64_decode ($string);
		$decrypted = '';
		for ($i = 0; $i < strlen ($encrypted); $i++) {
			 $decrypted .= chr (ord($encrypted[$i]) ^ ord($pad[$i]));
		}
		return $decrypted;
	}

	function sm_otp_create($length=100) {
		sm_mt_randomize();
		$pad = '';
		for ($i = 0; $i < $length; $i++) {
			 $pad .= chr(mt_rand(0,255));
		}
		return base64_encode($pad);
	}

	// This function initializes the random number generator fairly well.
	// It also only initializes it once, so you don't accidentally get
	// the same 'random' numbers twice in one session.
	function sm_mt_randomize() {
		global $REMOTE_PORT, $REMOTE_ADDR, $UNIQUE_ID;
		static $randomized;
		
		if ($randomized) {
			return;
		}
		
		// Global	
		sm_mt_seed((int)((double) microtime() * 1000000));
		sm_mt_seed(md5($REMOTE_PORT . $REMOTE_ADDR . getmypid()));
		
		// getrusage
		if (function_exists('getrusage')) {
			$dat = getrusage();
			$str = '';
			foreach ($dat as $k => $v) {
				$str .= $k . $v;
			}
			sm_mt_seed(md5($str));
		}
		
		// Apache-specific
		sm_mt_seed(md5($UNIQUE_ID));

		$randomized = 1;
	}

	/* 
	** Randomize the mt_rand() function.  Toss this in strings or
	** integers and it will seed the generator appropriately.
	** With strings, it is better to get them long. Use md5() to
	** lengthen smaller strings.
	*/
	function sm_mt_seed($val) {
		// if mt_getrandmax() does not return a 2^n - 1 number,
		// this might not work well.  This uses $max as a bitmask.
		$max = mt_getrandmax();
		 
		if (! is_int($val)) {
			if (function_exists('crc32')) {
				$val = crc32($val);
			} else {
				$str = $val;
				$pos = 0;
				$val = 0;
				$mask = $max / 2;
				$HighBit = $max ^ $mask;
				while ($pos < strlen($str)) {
					if ($val & $HighBit)
					{
						$val = (($val & $mask) << 1) + 1;
					}
					else
					{
						$val = ($val & $mask) << 1;
					}
						$val ^= $str[$pos];
						$pos ++;
				}
			}
		}

		if ($val < 0) {
			$val *= -1;
		}
		if ($val = 0) {
			return;
		}

		mt_srand(($val ^ mt_rand(0, $max)) & $max);
	}

?>
