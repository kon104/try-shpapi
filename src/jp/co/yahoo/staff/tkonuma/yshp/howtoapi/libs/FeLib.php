<?php

namespace myapp\jp\co\yahoo\staff\tkonuma\yshp\howtoapi\libs;

class FeLib
{

	// {{{ public static function empty($value)
	public static function empty($value)
	{
		$ret = false;
		if ($value === null) {
			$ret = true;
		} else
		if ($value === "") {
			$ret = true;
		}

		return $ret;
	}
	// }}}

	// {{{ public static function dumpVar($array, $title)
	public static function dumpVar($array, $title)
	{
		echo "<h3>$title</h3>" . PHP_EOL . "<pre>" . PHP_EOL;
		var_dump($array);
		echo "</pre>" . PHP_EOL;
		
		return;
	}
	// }}}


}

