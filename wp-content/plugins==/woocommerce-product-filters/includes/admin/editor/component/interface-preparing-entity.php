<?php

namespace WooCommerce_Product_Filter_Plugin\Admin\Editor\Component;

use WooCommerce_Product_Filter_Plugin\Entity,
	WooCommerce_Product_Filter_Plugin\Project\Project;

interface Preparing_Entity_Interface {
	public function preparing_entity( Entity $entity, Project $project );
}
