/*
 * Copyright (c) 2017 ViA-Online GmbH. All rights reserved.
 */

define(['ko'], function (ko) {
        'use strict';
        var red = ko.observable(0);
        var blue = ko.observable(0);
        var green = ko.observable(0);
        function randomNumber() {
            return Math.floor((Math.random() * 255) + 1);
        }
        function updateColour() {
            red(randomNumber());
            blue(randomNumber());
            green(randomNumber());
        }
        return {
            randomNumber: randomNumber,
            updateColour: updateColour,
            red: red,
            blue: blue,
            green: green
        };
    }
);