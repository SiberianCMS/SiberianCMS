//
//  AppDelegate.h
//  Siberian Angular
//
//  Created by Adrien Sala on 08/07/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import <UIKit/UIKit.h>
// #import "EVURLCache.h"
#import "RNCachingURLProtocol.h"
#import "Url.h"
#import "request.h"

@interface AppDelegate : UIResponder <UIApplicationDelegate, Request>

@property (strong, nonatomic) UIWindow *window;

@end
