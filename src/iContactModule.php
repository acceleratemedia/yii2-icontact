<?php

namespace bvb\icontact;

use Yii;

class iContactModule extends \yii\base\Module
{
	/**
	 * Set an alias for the root of this
	 * {@inheritdoc}
	 */
	public function init()
	{
		parent::init();
		Yii::setAlias('@bvb-icontact', __DIR__);
	}
}