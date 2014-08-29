//
//  ViewController.h
//  Siberian Angular
//
//  Created by Adrien Sala on 08/07/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "RNCachingURLProtocol.h"
#import "webViewController.h"
#import "common.h"

@interface ViewController : UIViewController <UIWebViewDelegate> {
    BOOL webViewIsLoaded;
    NSURL *webviewUrl;
}

@property (nonatomic, strong) IBOutlet UIWebView *webView;

@end
