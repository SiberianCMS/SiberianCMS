//
//  ViewController.h
//  Siberian Angular
//
//  Created by Adrien Sala on 08/07/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import <UIKit/UIKit.h>
#import <CoreLocation/CoreLocation.h>
#import "RNCachingURLProtocol.h"
#import "webViewController.h"
#import "common.h"

@interface ViewController : UIViewController <UIWebViewDelegate, CLLocationManagerDelegate> {
    BOOL webViewIsLoaded;
    NSURL *webviewUrl;
}

@property (nonatomic, strong) IBOutlet UIWebView *webView;
@property (nonatomic, strong) CLLocationManager *locationManager;

@end
