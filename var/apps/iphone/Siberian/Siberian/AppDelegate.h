//
//  AppDelegate.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <UIKit/UIKit.h>
#import <AVFoundation/AVFoundation.h>
#import "Request.h"
#import "common.h"
#import "url.h"
#import "mainViewController.h"

#import <math.h>

@interface AppDelegate : UIResponder <UIApplicationDelegate, Request> {
    UIImageView *splashScreen;
    UINavigationController *navController;
    
    NSString *scheme;
    NSString *domain;
    NSString *path;
    
}

@property (strong, nonatomic) UIWindow *window;

@property (strong, nonatomic) mainViewController *tabBarController;

@property (nonatomic, retain) UIImageView *splashScreen;

@property (readwrite) bool hasConnection;

@end
