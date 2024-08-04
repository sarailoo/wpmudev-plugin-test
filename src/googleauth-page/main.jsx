import { createRoot, render, StrictMode, createInterpolateElement } from '@wordpress/element';
import { Button, TextControl, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import $ from 'jquery';

import "./scss/style.scss"

const domElement = document.getElementById( window.wpmudevPluginTest.dom_element_id );

const WPMUDEV_PluginTest = () => {
    const [ clientId, setClientId ] = useState( '' );
    const [ clientSecret, setClientSecret ] = useState( '' );
    const [ message, setMessage ] = useState( '' );
    const [ noticeStatus, setNoticeStatus ] = useState( '' );
    const [ isLoading, setIsLoading ] = useState( false );

    const handleClick = async () => {
        setIsLoading( true );

        $.ajax( {
            type: 'POST',
            url: window.wpmudevPluginTest.siteUrl + 'wp-json/wpmudev/v1/auth/auth-url',
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', window.wpmudevPluginTest.nonce );
            },
            data: JSON.stringify( {
                client_id: clientId,
                client_secret: clientSecret,
            } ),
            contentType: 'application/json',
            success: function ( response ) {
                setIsLoading( false );

                if ( response.status === 'success' ) {
                    setMessage( response.message );
                    setNoticeStatus( 'success' );
                } else {
                    setMessage( response?.message );
                    setNoticeStatus('error');
                }
            },
            error: function ( xhr, status, error ) {
                setIsLoading(false);
                console.error( 'AJAX error:', status, error );
                setMessage( __( 'Failed to save settings. Please try again.', 'wpmudev-plugin-test' ) );
                setNoticeStatus( 'error' );
            }
        } );
    };

    return (
    <>
        <div class="sui-header">
            <h1 class="sui-header-title">
                { __( 'Settings', 'wpmudev-plugin-test' ) }
            </h1>
      </div>

        <div className="sui-box">

            <div className="sui-box-header">
                <h2 className="sui-box-title">{ __( 'Set Google credentials', 'wpmudev-plugin-test' ) }</h2>
            </div>

            <div className="sui-box-body">
                <div className="sui-box-settings-row">
                    <TextControl
                        help={createInterpolateElement(
                            __( 'You can get Client ID from <a>here</a>.', 'wpmudev-plugin-test' ),
                            {
                              a: <a href="https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid"/>,
                            }
                          )}
                        label={ __( 'Client ID', 'wpmudev-plugin-test' ) }
                        value={ clientId }
                        onChange={ ( value ) => setClientId( value ) }
                    />
                </div>

                <div className="sui-box-settings-row">
                    <TextControl
                        help={createInterpolateElement(
                            __( 'You can get Client Secret from <a>here</a>.', 'wpmudev-plugin-test' ),
                            {
                              a: <a href="https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid"/>,
                            }
                          )}
                        label={ __( 'Client Secret', 'wpmudev-plugin-test' ) }
                        type="password"
                        value={ clientSecret }
                        onChange={ ( value ) => setClientSecret( value ) }
                    />
                </div>

                <div className="sui-box-settings-row">
                <span>
                    { __(
                    'Please use this url ',
                    'wpmudev-plugin-test'
                    ) }
                    <em>{ window.wpmudevPluginTest.returnUrl }</em>
                    { __(
                    ' in your Google API\'s ',
                    'wpmudev-plugin-test'
                    ) }
                    <strong>{ __( 'Authorized redirect URIs', 'wpmudev-plugin-test' ) }</strong>
                    { __(
                    ' field.',
                    'wpmudev-plugin-test'
                    ) }
                </span>
                </div>
            </div>

            <div className="sui-box-footer">
                <div className="sui-actions-right">
                    <Button
                        variant="primary"
                        onClick={ handleClick }
                        disabled={ isLoading }
                    >
                        { isLoading ? __( 'Loading...', 'wpmudev-plugin-test' ) : __( 'Save', 'wpmudev-plugin-test' ) }
                    </Button>

                </div>
            </div>
            { message && (
                <Notice status={ noticeStatus } isDismissible={ false }>
                    { message }
                </Notice>
            ) }
        </div>

    </>
    );
}

if ( createRoot ) {
    createRoot( domElement ).render(<StrictMode><WPMUDEV_PluginTest/></StrictMode>);
} else {
    render( <StrictMode><WPMUDEV_PluginTest/></StrictMode>, domElement );
}
