import { createRoot, render, StrictMode } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useState } from 'react';
import $ from 'jquery';

import "./scss/style.scss"

const domElement = document.getElementById( window.wpmudevPluginTest.dom_element_id );

const WPMUDEV_PluginTest = () => {
    const [ isLoading, setIsLoading ] = useState( false );

    const handleScanPosts = async () => {
        setIsLoading( true );

        $.ajax( {
            type: 'post',
            url: window.wpmudevPluginTest.ajaxurl,
            data: {
                action: 'wpmudev_scan_posts',
                nonce: window.wpmudevPluginTest.wpmudevNonce,
            },
            success: function ( response ) {
                setIsLoading( false );
            },
            error: function ( xhr, status, error ) {
                setIsLoading( false );
                console.error( 'AJAX error:', status, error );
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
                <h2 className="sui-box-title">{ __( 'Scan Posts', 'wpmudev-plugin-test' ) }</h2>
            </div>

            <div className="sui-box-footer">
                <div className="sui-actions-right">
                    <Button
                        variant="primary"
                        onClick={ handleScanPosts }
                        disabled={ isLoading }
                    >
                        { isLoading ? __( 'Scanning...', 'wpmudev-plugin-test' ) : __( 'Scan', 'wpmudev-plugin-test' ) }
                    </Button>

                </div>
            </div>
        </div>

    </>
    );
}

if ( createRoot ) {
    createRoot( domElement ).render(<StrictMode><WPMUDEV_PluginTest/></StrictMode>);
} else {
    render( <StrictMode><WPMUDEV_PluginTest/></StrictMode>, domElement );
}
