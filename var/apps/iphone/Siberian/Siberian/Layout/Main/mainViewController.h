//
//  mainViewController.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <UIKit/UIKit.h>
#import <CoreLocation/CoreLocation.h>
#import <Quartzcore/Quartzcore.h>
#import <MediaPlayer/MediaPlayer.h>

#import "mapsViewController.h"
#import "moviePlayerViewController.h"
#import "youtubePlayerViewController.h"

#import "webViewController.h"
#import "loaderView.h"
#import "Request.h"
#import "common.h"
#import "url.h"


@interface mainViewController : UIViewController <UIWebViewDelegate, CLLocationManagerDelegate, Request, webViewControllerDelegate> {
    bool canLoadNotifs;
    bool webViewIsLoaded;
    
    CLLocationManager *locationManager;
}

@property (readwrite) bool isPreview;


@property (strong, nonatomic) IBOutlet loaderView *loader;
@property (strong, nonatomic) IBOutlet UIView *mainView;
@property (strong, nonatomic) IBOutlet UIWebView *wv;
@property (strong, nonatomic) NSArray *excludedUrls;

@property (strong, nonatomic) UIImageView *splashScreen;
@property (strong, nonatomic) UIImage *splashScreenImage;


@property (readwrite) bool canLoadNotifs;


- (void)appDidEnterForeground;
- (void)loadNotifs;



//@property (strong, nonatomic, retain) CLLocationManager *locationManager;


@end
