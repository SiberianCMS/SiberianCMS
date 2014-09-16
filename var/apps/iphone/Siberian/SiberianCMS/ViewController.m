//
//  ViewController.m
//  Siberian Angular
//
//  Created by Adrien Sala on 08/07/2014.
//  Copyright (c) 2014 Adrien Sala. All rights reserved.
//

#import "ViewController.h"

@interface ViewController ()

@end

@implementation ViewController

@synthesize webView, locationManager;

- (void)viewDidLoad
{
    [super viewDidLoad];
    
    webViewIsLoaded = NO;
    
    NSString *url = [[Url sharedInstance] get:@""];
    NSURLRequest *request = [NSURLRequest requestWithURL:[[NSURL alloc] initWithString:url]];
    webView.delegate = self;
    webView.scrollView.bounces = NO;
    [webView loadRequest:request];
}

- (void)viewDidUnload {
    [self setWebView:nil];
    [self setLocationManager:nil];
    [super viewDidUnload];
}


- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

#pragma clang diagnostic ignored "-Warc-performSelector-leaks"
- (BOOL)webView:(UIWebView *)webView shouldStartLoadWithRequest:(NSURLRequest *)request navigationType:(UIWebViewNavigationType)navigationType {
    
    NSString *url = [NSString stringWithFormat:@"%@", [request URL]];
    NSLog(@"url : %@", url);
    if(navigationType == UIWebViewNavigationTypeLinkClicked) {
        webviewUrl = [[NSURL alloc] initWithString:url];
        [self performSegueWithIdentifier:@"openWebview" sender:self];
        return NO;
    } else if([url rangeOfString:@"tel:"].location != NSNotFound) {
        NSLog(@"phone number : %@", [request URL]);
    } else if([url rangeOfString:@"app:"].location != NSNotFound) {
        
        NSArray *words = [url componentsSeparatedByString:@":"];
        SEL function = NSSelectorFromString([words lastObject]);
        if([self respondsToSelector:function]) {
            [self performSelector:function];
        }
        
        return NO;
        
    }
    
    return YES;
}

- (void)webViewDidFinishLoad:(UIWebView *)wv {
    if(isAtLeastiOS7()) {
        [webView stringByEvaluatingJavaScriptFromString:@"angular.element(document.body).addClass('iOS7')"];
    }
    
    NSString *jsonString = [webView stringByEvaluatingJavaScriptFromString:@"JSON.stringify(window.colors)"];
    NSData *jsonData = [jsonString dataUsingEncoding:NSUTF8StringEncoding];
    NSDictionary *colors = [NSJSONSerialization JSONObjectWithData:jsonData options:NSJSONReadingAllowFragments error:nil];
    [common setColors:colors];
}

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    if ([[segue identifier] isEqualToString:@"openWebview"]) {
        [segue.destinationViewController setWebViewUrl:webviewUrl];
    }
}

- (void)requestLocation {
    
    NSLog(@"locationServicesEnabled: %@", [CLLocationManager locationServicesEnabled] ? @"YES":@"NO");
    
    locationManager = [[CLLocationManager alloc] init];
    locationManager.distanceFilter = kCLDistanceFilterNone;
    locationManager.desiredAccuracy = kCLLocationAccuracyBestForNavigation;
    locationManager.delegate = self;
    
    if([locationManager respondsToSelector:@selector(requestWhenInUseAuthorization)]) {
        [locationManager requestWhenInUseAuthorization];
    }
    [locationManager startUpdatingLocation];
}

- (void)locationManager:(CLLocationManager *)manager didUpdateLocations:(NSArray *)location {
    CLLocation *currentLocation = [location objectAtIndex:0];
    [locationManager stopUpdatingLocation];
    NSLog(@"position: %@", currentLocation);
    NSString *coordinates = [[NSString alloc] initWithFormat:@"setCoordinates(%f, %f)", currentLocation.coordinate.latitude, currentLocation.coordinate.longitude];
    [webView stringByEvaluatingJavaScriptFromString:coordinates];
    
}
- (void)locationManager:(CLLocationManager *)manager didUpdateToLocation:(CLLocation *)newLocation fromLocation:(CLLocation *)oldLocation {
    
    [locationManager stopUpdatingLocation];
    NSLog(@"position: %@", newLocation);
    NSString *coordinates = [[NSString alloc] initWithFormat:@"setCoordinates(%f, %f)", newLocation.coordinate.latitude, newLocation.coordinate.longitude];
    [webView stringByEvaluatingJavaScriptFromString:coordinates];
    
}

- (void)locationManager:(CLLocationManager *)manager didFailWithError:(NSError *)error {
    [webView stringByEvaluatingJavaScriptFromString:@"setCoordinates()"];
    NSLog(@"Can't access user's position");
}

- (void)removeBadge {
    [UIApplication sharedApplication].applicationIconBadgeNumber = 0;
}



@end
