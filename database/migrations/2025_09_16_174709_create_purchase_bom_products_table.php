'use strict';

var $TypeError = require('es-errors/type');

var IsCallable = require('./IsCallable');
var IsConstructor = require('./IsConstructor');

// https://262.ecma-international.org/6.0/#sec-newpromisecapability

module.exports = function NewPromiseCapability(C) {
	if (!IsConstructor(C)) {
		throw new $TypeError('C must be a constructor'); // step 1
	}

	var resolvingFunctions = { '[[Resolve]]': void undefined, '[[Reject]]': void undefined }; // step 3

	var promise = new C(function (resolve, reject) { // steps 4-5
		if (typeof resolvingFunctions['[[Resolve]]'] !== 'undefined' || typeof resolvingFunctions['[[Reject]]'] !== 'undefined') {
			throw new $TypeError('executor has already been