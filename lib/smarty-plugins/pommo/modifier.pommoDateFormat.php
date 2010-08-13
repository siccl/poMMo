<?php

function smarty_modifier_pommoDateFormat($int)
{
	return Pommo_Helper::timeToStr($int);
}

?>
