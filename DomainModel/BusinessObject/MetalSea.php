<?php
class MetalSea extends Sea
{
      public function isAttackedBoss()
      {
      		$numArmorPillar = count($this->Monster[$this->RoundNum]) - 1;
      		if ($numArmorPillar > 0)
      			return false;
      		return true;
      }
}