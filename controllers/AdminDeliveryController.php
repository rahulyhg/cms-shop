<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 28.08.2015
 */
namespace skeeks\cms\shop\controllers;

use skeeks\cms\components\Cms;
use skeeks\cms\grid\BooleanColumn;
use skeeks\cms\grid\ImageColumn2;
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopBuyer;
use skeeks\cms\shop\models\ShopDelivery;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

/**
 * Class AdminTaxController
 * @package skeeks\cms\shop\controllers
 */
class AdminDeliveryController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \Yii::t('skeeks/shop/app', 'Delivery services');
        $this->modelShowAttribute       = "name";
        $this->modelClassName           = ShopDelivery::className();

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
            [
                'index' =>
                [
                    "gridConfig" =>
                    [
                        'settingsData' =>
                        [
                            'order' => SORT_ASC,
                            'orderBy' => "priority",
                        ]
                    ],

                    "columns"      => [
                        'name',
                        'priority',

                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "shopPaySystems",
                            'filter'        => false,
                            'value'         => function(ShopDelivery $model)
                            {
                                return implode(", ", ArrayHelper::map($model->shopPaySystems, 'id', 'name'));
                            }
                        ],
                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "price",
                            'format'     => 'raw',
                            'filter'        => false,
                            'value'         => function(ShopDelivery $model)
                            {
                                return \Yii::$app->money->intlFormatter()->format($model->money);
                            }
                        ],
                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => "active"
                        ]
                    ],
                ]
            ]
        );
    }

}
