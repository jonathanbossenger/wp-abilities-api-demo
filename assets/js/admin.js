( function( wp ) {
	'use strict';

	// Check if wp.abilities is available
	if ( ! wp || ! wp.abilities || ! wp.abilities.executeAbility ) {
		console.error( 'WP Abilities API is not available. Please ensure the Abilities API plugin is installed and activated.' );
		return;
	}

	const { executeAbility } = wp.abilities;

	/**
	 * Initialize the admin page functionality.
	 */
	function init() {
		const executeButtons = document.querySelectorAll( '.execute-ability' );

		executeButtons.forEach( function( button ) {
			button.addEventListener( 'click', function() {
				const abilityName = this.getAttribute( 'data-ability' );
				const section = this.closest( '.ability-section' );
				handleAbilityExecution( abilityName, section );
			} );
		} );
	}

	/**
	 * Handle the execution of an ability.
	 *
	 * @param {string} abilityName The name of the ability to execute.
	 * @param {HTMLElement} section The section element containing the ability.
	 */
	function handleAbilityExecution( abilityName, section ) {
		const loadingEl = section.querySelector( '.ability-loading' );
		const outputEl = section.querySelector( '.ability-output' );
		const button = section.querySelector( '.execute-ability' );

		// Prepare input based on ability
		const input = prepareAbilityInput( abilityName, section );

		if ( input === null ) {
			displayError( outputEl, 'Please fill in all required fields.' );
			return;
		}

		// Show loading state
		button.disabled = true;
		loadingEl.style.display = 'block';
		outputEl.innerHTML = '';

		// Execute the ability
		executeAbility( abilityName, input )
			.then( function( result ) {
				displayOutput( outputEl, result );
			} )
			.catch( function( error ) {
				displayError( outputEl, error.message || 'An error occurred while executing the ability.' );
			} )
			.finally( function() {
				button.disabled = false;
				loadingEl.style.display = 'none';
			} );
	}

	/**
	 * Prepare input data for an ability based on its requirements.
	 *
	 * @param {string} abilityName The name of the ability.
	 * @param {HTMLElement} section The section element containing the ability.
	 * @return {Object|null} The input object or null if validation fails.
	 */
	function prepareAbilityInput( abilityName, section ) {
		let input = {};

		switch ( abilityName ) {
			case 'site/site-info':
			case 'plugins/get-plugins':
			case 'debug/clear-log':
				// No input required
				break;

			case 'debug/read-log':
				const linesInput = section.querySelector( 'input[name="lines"]' );
				if ( linesInput ) {
					const lines = parseInt( linesInput.value, 10 );
					if ( lines >= 1 && lines <= 1000 ) {
						input.lines = lines;
					}
				}
				break;

			case 'post/create-post':
				const titleInput = section.querySelector( 'input[name="title"]' );
				const contentInput = section.querySelector( 'textarea[name="content"]' );
				const statusInput = section.querySelector( 'select[name="status"]' );

				if ( ! titleInput || ! titleInput.value.trim() ) {
					return null;
				}
				if ( ! contentInput || ! contentInput.value.trim() ) {
					return null;
				}

				input.title = titleInput.value.trim();
				input.content = contentInput.value.trim();
				if ( statusInput ) {
					input.status = statusInput.value;
				}
				break;

			case 'plugin-check/check-security':
				const pluginSlugInput = section.querySelector( 'input[name="plugin_slug"]' );

				if ( ! pluginSlugInput || ! pluginSlugInput.value.trim() ) {
					return null;
				}

				input.plugin_slug = pluginSlugInput.value.trim();
				break;
		}

        // if the input is an empty object, set it to undefined
        // Abilities with no input_schema defined should not receive any input
        input = Object.keys( input ).length === 0 ? undefined : input;
		return input;
	}

	/**
	 * Display the output of an ability execution.
	 *
	 * @param {HTMLElement} outputEl The output element.
	 * @param {Object} result The result object from the ability execution.
	 */
	function displayOutput( outputEl, result ) {
		const pre = document.createElement( 'pre' );
		pre.className = 'ability-result';
		pre.textContent = JSON.stringify( result, null, 2 );
		outputEl.innerHTML = '';
		outputEl.appendChild( pre );
	}

	/**
	 * Display an error message.
	 *
	 * @param {HTMLElement} outputEl The output element.
	 * @param {string} message The error message.
	 */
	function displayError( outputEl, message ) {
		const errorDiv = document.createElement( 'div' );
		errorDiv.className = 'notice notice-error';
		errorDiv.innerHTML = '<p><strong>Error:</strong> ' + escapeHtml( message ) + '</p>';
		outputEl.innerHTML = '';
		outputEl.appendChild( errorDiv );
	}

	/**
	 * Escape HTML to prevent XSS.
	 *
	 * @param {string} text The text to escape.
	 * @return {string} The escaped text.
	 */
	function escapeHtml( text ) {
		const div = document.createElement( 'div' );
		div.textContent = text;
		return div.innerHTML;
	}

	// Initialize when DOM is ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

}( wp ) );
