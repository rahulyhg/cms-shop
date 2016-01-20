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
use skeeks\cms\grid\CreatedAtColumn;
use skeeks\cms\grid\SiteColumn;
use skeeks\cms\grid\UserColumnData;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\CmsAgent;
use skeeks\cms\models\CmsContent;
use skeeks\cms\models\CmsSite;
use skeeks\cms\modules\admin\actions\modelEditor\AdminMultiModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelEditAction;
use skeeks\cms\modules\admin\actions\modelEditor\AdminOneModelUpdateAction;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use skeeks\cms\modules\admin\traits\AdminModelEditorStandartControllerTrait;
use skeeks\cms\shop\models\ShopAffiliate;
use skeeks\cms\shop\models\ShopAffiliatePlan;
use skeeks\cms\shop\models\ShopContent;
use skeeks\cms\shop\models\ShopExtra;
use skeeks\cms\shop\models\ShopOrder;
use skeeks\cms\shop\models\ShopOrderStatus;
use skeeks\cms\shop\models\ShopPersonType;
use skeeks\cms\shop\models\ShopTax;
use skeeks\cms\shop\models\ShopVat;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminExtraController
 * @package skeeks\cms\shop\controllers
 */
class AdminOrderController extends AdminModelEditorController
{
    use AdminModelEditorStandartControllerTrait;

    public function init()
    {
        $this->name                     = \skeeks\cms\shop\Module::t('app', 'Orders');
        $this->modelShowAttribute       = "id";
        $this->modelClassName           = ShopOrder::className();

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
                    "columns"               => [
                        [
                            'class'     => CreatedAtColumn::className(),
                        ],

                        'id',

                        [
                            'class'     => DataColumn::className(),
                            'attribute'     => 'status_code',
                            'format'     => 'raw',
                            'filter'     => ArrayHelper::map(ShopOrderStatus::find()->all(), 'code', 'name'),
                            'value'     => function(ShopOrder $order)
                            {
                                return Html::label($order->status->name, null, [
                                    'style' => "background: {$order->status->color}",
                                    'class' => "label"
                                ]) . "<br />" .
                                    Html::tag("small", \Yii::$app->formatter->asDatetime($order->status_at) . " (" . \Yii::$app->formatter->asRelativeTime($order->status_at) . ")")
                                ;
                            }
                        ],

                        /*[
                            'class'     => DataColumn::className(),
                            'attribute' => 'buyer_id',
                            'format'    => 'raw',
                            'value'     => function(ShopOrder $model)
                            {
                                if (!$model->buyer)
                                {
                                    return null;
                                }

                                return Html::a($model->buyer->name . " [{$model->buyer->id}]", UrlHelper::construct('shop/admin-buyer/related-properties', ['pk' => $model->buyer->id])->enableAdmin()->toString());
                            }
                        ],*/

                        [
                            'class'         => BooleanColumn::className(),
                            'attribute'     => 'payed',
                            'format'        => 'raw',
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "canceled",
                            'format'        => "raw",
                            'filter'        => [
                                'Y' => \Yii::t('app', 'Yes'),
                                'N' => \Yii::t('app', 'No'),
                            ],
                            'value'         => function(ShopOrder $shopOrder)
                            {
                               return $shopOrder->canceled == "Y" ? \Yii::t('app', 'Yes') : \Yii::t('app', 'No');
                            },
                        ],
                        [
                            'class'         => DataColumn::className(),
                            'attribute'     => "user_id",
                            'label'         => \skeeks\cms\shop\Module::t('app', 'Buyer'),
                            'format'        => "raw",
                            'value'         => function(ShopOrder $shopOrder)
                            {
                               return (new \skeeks\cms\shop\widgets\AdminBuyerUserWidget(['user' => $shopOrder->user]))->run();
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => false,
                            'format'        => 'raw',
                            'label'         => \skeeks\cms\shop\Module::t('app', 'Good'),
                            'value'         => function(ShopOrder $model)
                            {
                                if ($model->shopBaskets)
                                {
                                    $result = [];
                                    foreach ($model->shopBaskets as $shopBasket)
                                    {
                                        $money = \Yii::$app->money->intlFormatter()->format($shopBasket->money);
                                        $result[] = Html::a($shopBasket->name, $shopBasket->product->cmsContentElement->url, ['target' => '_blank']) . <<<HTML
  — $shopBasket->quantity $shopBasket->measure_name
HTML;

                                    }
                                    return implode('<hr style="margin: 0px;"/>', $result);
                                }
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'format'        => 'raw',
                            'attribute'     => 'price',
                            'label'         => \skeeks\cms\shop\Module::t('app', 'Sum'),
                            'value'         => function(ShopOrder $model)
                            {
                                return \Yii::$app->money->intlFormatter()->format($model->money);
                            },
                        ],

                        [
                            'class'         => DataColumn::className(),
                            'filter'        => ArrayHelper::map(CmsSite::find()->active()->all(), 'id', 'name'),
                            'attribute'     => 'site_id',
                            'format'        => 'raw',
                            'visible'       => false,
                            'label'         => \skeeks\cms\shop\Module::t('app', 'Site'),
                            'value'         => function(ShopOrder $model)
                            {
                                return $model->site->name . " [{$model->site->code}]";
                            },
                        ],
                    ],
                ],

                "view" =>
                [
                    'class'         => AdminOneModelEditAction::className(),
                    "name"         => \Yii::t('app',"Информация"),
                    "icon"          => "glyphicon glyphicon-eye-open",
                    "priority"      => 5,
                    "callback"      => [$this, 'view'],
                ],

            ]
        );
    }

    public function view()
    {
        return $this->render($this->action->id, [
            'model' => $this->model
        ]);
    }


}
