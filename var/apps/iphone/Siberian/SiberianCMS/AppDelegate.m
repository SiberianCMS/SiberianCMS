//
//  AppDelegate.m
//  Siberian Angular
//
//  Created by Adrien Sala on 08/07/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import "AppDelegate.h"

@implementation AppDelegate

- (BOOL)application:(UIApplication *)application didFinishLaunchingWithOptions:(NSDictionary *)launchOptions
{
    
    BOOL hasConnection = NO;
    NSError *error;
    
    // Check the connection
    NSString *url = [[Url sharedInstance] getBase:@"check_connection.php"];;
    NSLog(@"loading url: %@", url);
    NSString *hasMenus = [NSString stringWithContentsOfURL:[NSURL URLWithString:url] encoding:NSUTF8StringEncoding error:&error];
    if(error.code == 0 && ([hasMenus isEqualToString:@"1"] || [hasMenus isEqualToString:@"0"])) {
        hasConnection = YES;
    }

    // Set the User Agent
    NSString* userAgent = [[[UIWebView alloc] init] stringByEvaluatingJavaScriptFromString:@"navigator.userAgent"];
    userAgent = [userAgent stringByAppendingString:@" Type/siberian.application"];
    NSDictionary *dictionary = [NSDictionary dictionaryWithObjectsAndKeys:userAgent, @"UserAgent", nil];
    [[NSUserDefaults standardUserDefaults] registerDefaults:dictionary];

    
    // Prepare the cache
    useCache = !hasConnection;
    [[NSURLCache sharedURLCache] removeAllCachedResponses];
    [[NSURLCache sharedURLCache] setDiskCapacity:0];
    [[NSURLCache sharedURLCache] setMemoryCapacity:0];
    [NSURLProtocol registerClass:[RNCachingURLProtocol class]];
    
    // Create an identifier if not exists
    NSUserDefaults *dict = [NSUserDefaults standardUserDefaults];
    NSString *identifier = [dict stringForKey:@"identifier"];
    if(identifier.length == 0) {
        CFUUIDRef uuidRef = CFUUIDCreate(NULL);
        CFStringRef uuidStringRef = CFUUIDCreateString(NULL, uuidRef);
        CFRelease(uuidRef);
        identifier = (__bridge NSString *)uuidStringRef;
        [dict setObject:identifier forKey:@"identifier"];
        [dict synchronize];
    }

    // Prepare the push notifications
    if(hasConnection) {
        // Préparation du push
        if ([application respondsToSelector:@selector(registerUserNotificationSettings:)]) {
            // use registerUserNotificationSettings
            [[UIApplication sharedApplication] registerUserNotificationSettings:[UIUserNotificationSettings settingsForTypes:(UIUserNotificationTypeSound | UIUserNotificationTypeAlert | UIUserNotificationTypeBadge) categories:nil]];
            [[UIApplication sharedApplication] registerForRemoteNotifications];
            
        } else {
            [[UIApplication sharedApplication] registerForRemoteNotificationTypes:
             (UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeSound | UIRemoteNotificationTypeAlert)];
        }
    }

    
    return YES;
}

/* /PUSH */
- (void)application:(UIApplication *)application didRegisterForRemoteNotificationsWithDeviceToken:(NSData *)token {
    
#if !TARGET_IPHONE_SIMULATOR
    
    NSString *appName = [[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleDisplayName"];
    NSString *appVersion = [[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleVersion"];
    
    NSUInteger rntypes = [[UIApplication sharedApplication] enabledRemoteNotificationTypes];
    
    NSString *pushBadge = @"disabled";
    NSString *pushAlert = @"disabled";
    NSString *pushSound = @"disabled";
    
    if ([[NSUserDefaults standardUserDefaults] objectForKey:@"isFirstLaunch"] == nil) {
        pushBadge = @"enabled";
        pushAlert = @"enabled";
        pushSound = @"enabled";
        [[NSUserDefaults standardUserDefaults] setBool:NO forKey:@"isFirstLaunch"];
    }
    else if([application respondsToSelector:@selector(currentUserNotificationSettings)]) {
        // UIUserNotificationSettings *currentSettings = [application currentUserNotificationSettings];
        pushBadge = @"enabled";
        pushAlert = @"enabled";
        pushSound = @"enabled";
    } else {
        
        if(rntypes == UIRemoteNotificationTypeBadge){
            pushBadge = @"enabled";
        }
        else if(rntypes == UIRemoteNotificationTypeAlert) {
            pushAlert = @"enabled";
        }
        else if(rntypes == UIRemoteNotificationTypeSound) {
            pushSound = @"enabled";
        }
        else if(rntypes == ( UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeAlert)) {
            pushBadge = @"enabled";
            pushAlert = @"enabled";
        }
        else if(rntypes == ( UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeSound)) {
            pushBadge = @"enabled";
            pushSound = @"enabled";
        }
        else if(rntypes == ( UIRemoteNotificationTypeAlert | UIRemoteNotificationTypeSound)) {
            pushAlert = @"enabled";
            pushSound = @"enabled";
        }
        else if(rntypes == ( UIRemoteNotificationTypeBadge | UIRemoteNotificationTypeAlert | UIRemoteNotificationTypeSound)) {
            pushBadge = @"enabled";
            pushAlert = @"enabled";
            pushSound = @"enabled";
        }
    }
    // Get the users Device Model, Display Name, Token & Version Number
    UIDevice *dev = [UIDevice currentDevice];
    
    NSUserDefaults *dict = [NSUserDefaults standardUserDefaults];
    NSString *identifier = [dict stringForKey:@"identifier"];
    NSString *deviceName = dev.name;
    NSString *deviceModel = dev.model;
    NSString *deviceSystemVersion = dev.systemVersion;
    
    // Prepare the Device Token for Registration (remove spaces and < >)
    NSString *deviceToken = [[[[token description]
                               stringByReplacingOccurrencesOfString:@"<"withString:@""]
                              stringByReplacingOccurrencesOfString:@">" withString:@""]
                             stringByReplacingOccurrencesOfString: @" " withString: @""];
    
    NSMutableDictionary *postDatas = [NSMutableDictionary dictionary];
    [postDatas setObject:appName forKey:@"app_name"];
    [postDatas setObject:appVersion forKey:@"app_version"];
    [postDatas setObject:identifier forKey:@"device_uid"];
    [postDatas setObject:deviceToken forKey:@"device_token"];
    [postDatas setObject:deviceName forKey:@"device_name"];
    [postDatas setObject:deviceModel forKey:@"device_model"];
    [postDatas setObject:deviceSystemVersion forKey:@"device_version"];
    [postDatas setObject:pushBadge forKey:@"push_badge"];
    [postDatas setObject:pushAlert forKey:@"push_alert"];
    [postDatas setObject:pushSound forKey:@"push_sound"];
    
    Request *request = [Request alloc];
    request.delegate = self;
    
    [request postDatas:postDatas withUrl:@"push/iphone/registerdevice/"];
    
    
#endif
    
}

- (void) connectionDidFinish:(NSData *)datas {
    
    // Récupère le badge
    NSString *badge = [[NSString alloc] initWithData:datas encoding:NSUTF8StringEncoding];
    [UIApplication sharedApplication].applicationIconBadgeNumber = [badge intValue];
}

- (void) connectionDidFail {
    NSLog(@"connexion échouée");
}

/**
 * Remote Notification Received while application was open.
 */
- (void)application:(UIApplication *)application didReceiveRemoteNotification:(NSDictionary *)userInfo {
    
#if !TARGET_IPHONE_SIMULATOR
    
    // Récupère les infos de la notification
    NSDictionary *apsInfo = [userInfo objectForKey:@"aps"];
    int badge = [[apsInfo objectForKey:@"badge"] intValue];
    badge++;
/*
    mainViewController *controller = (mainViewController *) self.window.rootViewController;
    controller.canLoadNotifs = YES;
    if (application.applicationState == UIApplicationStateActive) {
        // Met à jour la sidebar de gauche
        [controller loadNotifs];
    }
    else {
*/
        // Inutil car déjà mis à jour
        application.applicationIconBadgeNumber = badge;
//    }
    
#endif
}

- (void) notifsDidShow:(NSNotification *)pNotification {
    
    // Remet le badge à 0
    [UIApplication sharedApplication].applicationIconBadgeNumber = 0;
    
}

- (void)application:(UIApplication *)application didFailToRegisterForRemoteNotificationsWithError:(NSError *)error {
//    NSLog(@"Push échoué");
}

/* /PUSH */

- (void)applicationWillResignActive:(UIApplication *)application
{
    // Sent when the application is about to move from active to inactive state. This can occur for certain types of temporary interruptions (such as an incoming phone call or SMS message) or when the user quits the application and it begins the transition to the background state.
    // Use this method to pause ongoing tasks, disable timers, and throttle down OpenGL ES frame rates. Games should use this method to pause the game.
}

- (void)applicationDidEnterBackground:(UIApplication *)application
{
    // Use this method to release shared resources, save user data, invalidate timers, and store enough application state information to restore your application to its current state in case it is terminated later. 
    // If your application supports background execution, this method is called instead of applicationWillTerminate: when the user quits.
}

- (void)applicationWillEnterForeground:(UIApplication *)application
{
    // Called as part of the transition from the background to the inactive state; here you can undo many of the changes made on entering the background.
}

- (void)applicationDidBecomeActive:(UIApplication *)application
{
    // Restart any tasks that were paused (or not yet started) while the application was inactive. If the application was previously in the background, optionally refresh the user interface.
}

- (void)applicationWillTerminate:(UIApplication *)application
{
    // Called when the application is about to terminate. Save data if appropriate. See also applicationDidEnterBackground:.
}

@end
