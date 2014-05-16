//
//  webViewController.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <UIKit/UIKit.h>
#import "loaderView.h"
#import "common.h"

@protocol webViewControllerDelegate

@optional

- (void) facebookDidClose:(BOOL)isLoggedIn;

@end

@interface webViewController : UIViewController <UIWebViewDelegate> {
    id <NSObject, webViewControllerDelegate> delegate;
    NSURL *webViewUrl;
    loaderView *loader;
}

@property (retain) id <NSObject, webViewControllerDelegate> delegate;

@property (strong, nonatomic) NSURL *webViewUrl;
@property (strong, nonatomic) IBOutlet UIWebView *wv;
@property (strong, nonatomic) IBOutlet UIToolbar *toolbar;
@property (strong, nonatomic) IBOutlet UIBarButtonItem *btnBack;
@property (strong, nonatomic) IBOutlet UIBarButtonItem *btnForth;
@property (strong, nonatomic) IBOutlet UIBarButtonItem *btnStop;
@property (strong, nonatomic) IBOutlet UIBarButtonItem *btnRefresh;

@property (strong, nonatomic) IBOutlet loaderView *loader;

- (IBAction)closeModal:(id)sender;
- (IBAction)goBack:(id)sender;
- (IBAction)goForth:(id)sender;
- (IBAction)stop:(id)sender;
- (IBAction)refresh:(id)sender;

@end
