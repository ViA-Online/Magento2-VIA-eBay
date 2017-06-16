/*
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

/*eslint-disable no-undef*/
define(['uiComponent', 'ko', 'jQuery'], function (Component, ko, jQuery) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            this.sayHello = "Hello this is content populated with KO!";
        }
    });
});