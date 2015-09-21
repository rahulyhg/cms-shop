/*!
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.04.2015
 */
(function(sx, $, _)
{
    sx.createNamespace('classes.shop', sx);

    /**
     * @events:
     * beforeAddProduct
     * addProduct
     *
     * beforeRemoveBasket
     * removeBasket
     *
     * beforeUpdateBasket
     * updateBasket
     *
     * change
     *
     */
    sx.classes.shop._App = sx.classes.Component.extend({

        _init: function()
        {
            var self = this;
            this.carts = [];

            this.bind('removeBasket addProduct updateBasket', function(e, data)
            {
                self.trigger('change', {
                    'Shop' : this
                });
            });
        },

        /**
         * @returns {sx.classes.AjaxQuery}
         */
        ajaxQuery: function()
        {
            return sx.ajax.preparePostQuery('/');
        },

        /**
         * @param Cart
         */
        registerCart: function(Cart)
        {
            if (!Cart instanceof sx.classes.shop._Cart)
            {
                throw new Error("Cart object must be instanceof sx.classes.shop._Cart");
            }

            this.carts.push(Cart);
        },

        /**
         * Добавление продукта в корзину
         * @param product_id
         * @param quantity
         */
        addProduct: function(product_id, quantity)
        {
            var self = this;

            this.trigger('beforeAddProduct', {
                'product_id'    : product_id,
                'quantity'      : quantity,
            });

            var ajax = this.ajaxQuery().setUrl(this.get('backend-add-product'));

            ajax.setData({
                'product_id'    : Number(product_id),
                'quantity'      : Number(quantity),
            });

            ajax.onSuccess(function(e, data)
            {
                self.trigger('addProduct', {
                    'product_id'    : product_id,
                    'quantity'      : quantity,
                    'response'      : data.response,
                });
            });

            ajax.execute();
        },


        removeBasket: function(basket_id)
        {
            var self = this;

            this.trigger('beforeRemoveBasket', {
                'basket_id' : basket_id,
            });

            var ajax = this.ajaxQuery().setUrl(this.get('backend-remove-basket'));

            ajax.setData({
                'basket_id' : Number(basket_id),
            });

            ajax.onSuccess(function(e, data)
            {
                self.trigger('removeBasket', {
                    'basket_id'    : basket_id,
                    'response'     : data.response,
                });
            });

            ajax.execute();
        },

        updateBasket: function(basket_id, quantity)
        {
            var self = this;

            this.trigger('beforeUpdateBasket', {
                'basket_id'     : basket_id,
                'quantity'      : quantity,
            });

            var ajax = this.ajaxQuery().setUrl(this.get('backend-update-basket'));

            ajax.setData({
                'basket_id' : Number(basket_id),
                'quantity'  : Number(quantity),
            });

            ajax.onSuccess(function(e, data)
            {
                self.trigger('updateBasket', {
                    'basket_id'    : basket_id,
                    'quantity'    : quantity,
                    'response'     : data.response,
                });
            });

            ajax.execute();
        },
    });

    sx.classes.shop.App = sx.classes.shop._App.extend({});

})(sx, sx.$, sx._);