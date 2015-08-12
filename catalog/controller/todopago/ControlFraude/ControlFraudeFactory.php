<?php
class ControlFraudeFactory {

	const RETAIL = "Retail";
	const SERVICE = "Service";
	const DIGITAL_GOODS = "Digital Goods";
	const TICKETING = "Ticketing";

	public static function getControlFraudeExtractor($vertical, $order, $customer, $model){
		$instance;
		switch ($vertical) {
			case ControlFraudeFactory::RETAIL:
				$instance = new ControlFraude_Retail($order, $customer, $model);
			break;
			
			case ControlFraudeFactory::SERVICE:
				$instance = new ControlFraude_Service($order, $customer, $model);
			break;
			
			case ControlFraudeFactory::DIGITAL_GOODS:
				$instance = new ControlFraude_DigitalGoods($order, $customer, $model);
			break;

			default:
				$instance = new ControlFraude_Retail($order, $customer, $model);
			break;
		}
		return $instance;
	}
}