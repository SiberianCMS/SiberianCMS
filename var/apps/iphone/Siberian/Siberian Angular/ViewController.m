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

@synthesize webView;

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

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (BOOL)webView:(UIWebView *)webView shouldStartLoadWithRequest:(NSURLRequest *)request navigationType:(UIWebViewNavigationType)navigationType {
    
    NSString *url = [NSString stringWithFormat:@"%@", [request URL]];
    NSLog(@"url : %@", url);
    if(navigationType == UIWebViewNavigationTypeLinkClicked) {
        webviewUrl = [[NSURL alloc] initWithString:url];
        [self performSegueWithIdentifier:@"openWebview" sender:self];
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


@end
