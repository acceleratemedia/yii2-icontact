<?php

namespace bvb\icontact\backend\controllers;

use bvb\user\backend\controllers\traits\AdminAccess;
use yii\web\Controller;

/**
 * ApiController has actions to show helpful data for testing API connaction
 * and functionality
 */
class ApiController extends Controller
{
    /**
     * Implement AccessControl that requires admin role to access actions
     */
    use AdminAccess;

    /**
     * {@inheritdoc}
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}