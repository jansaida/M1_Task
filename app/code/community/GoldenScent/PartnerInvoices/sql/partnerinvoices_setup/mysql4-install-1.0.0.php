<?php
$installer = $this;
$installer->startSetup();

$installer->addAttribute("order", "partner_name", array("type"=>"varchar"));
$installer->endSetup();
	 