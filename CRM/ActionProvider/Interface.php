<?php

/**
 * Interface for an ActionProvider.
 */
 interface CRM_ActionProvider_Interface {
 	
	/**
	 * Execute the action.
	 */
	public function execute(CRM_ActionProvider_ParameterInterface $inputParameters);
	
 }