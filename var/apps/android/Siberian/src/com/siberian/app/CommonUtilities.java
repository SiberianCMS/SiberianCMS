/*
 * Copyright 2012 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.siberian.app;

/**
 * Helper class providing methods and constants common to other classes in the
 * app.
 */
public final class CommonUtilities {

	static final String SERVEUR_URL = "http://www.siberiancms.com/";
	
    static final String REGISTER_DEVICE_URL = "push/android/registerdevice";
    
    static final String MARK_DISPLAYED_URL = "push/android/markdisplayed";
    
    static final String UPDATE_POSITION_URL = "push/android/updateposition";

    static final String SENDER_ID = "";

    /**
     * Tag used on log messages.
     */
    static final String TAG = "GCMRegistration";

    /**
     * Intent used to display a message in the screen.
     */
    static final String DISPLAY_MESSAGE_ACTION =
            "com.siberian.app.DISPLAY_MESSAGE";

    /**
     * Intent's extra that contains the message to be displayed.
     */
    static final String EXTRA_MESSAGE = "message";

}
