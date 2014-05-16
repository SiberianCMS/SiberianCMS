//
//  mainViewController.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "mainViewController.h"

@interface mainViewController ()

@end

@implementation mainViewController
@synthesize isPreview;
@synthesize mainView, wv, loader, excludedUrls;
@synthesize splashScreen, splashScreenImage;
@synthesize canLoadNotifs;

//@synthesize locationManager;

- (void)viewDidLoad
{
    
    self.view.backgroundColor = [UIColor blackColor];
    
    if(!isPreview) {
        locationManager = [[CLLocationManager alloc] init];
        locationManager.delegate = self;
        [locationManager startMonitoringSignificantLocationChanges];
//        [locationManager startUpdatingLocation];
    }
    
    webViewIsLoaded  = NO;
    
    [[NSURLCache sharedURLCache] removeAllCachedResponses];
    
    // Préparation du Splash Screen
    splashScreen = [UIImageView alloc];
    if(splashScreenImage) {
        splashScreen = [splashScreen initWithImage:splashScreenImage];
    }
    else if(isScreeniPhone5()) {
        splashScreen = [splashScreen initWithImage:[UIImage imageNamed:@"Default-568h@2x.png"]];
    }
    else {
        splashScreen = [splashScreen initWithImage:[UIImage imageNamed:@"Default@2x.png"]];
    }
    
    CGRect screenBounds = [UIScreen mainScreen].bounds;
    
    splashScreen.frame = CGRectMake(0, isAtLeastiOS7()?0:-19, screenBounds.size.width, screenBounds.size.height);
    
    [self.view addSubview:splashScreen];
    [self.view bringSubviewToFront:splashScreen];
    
    [super viewDidLoad];
    
    [wv.scrollView setDelaysContentTouches:NO];
    wv.mediaPlaybackRequiresUserAction = NO;
    
    // Créé et affiche le loader
    loader = [[loaderView alloc] initWithFrame:CGRectMake(0, 0, screenBounds.size.width, screenBounds.size.height)];
    // Ajoute le loader à la vue en cours
    [self.view addSubview:loader];
    [self.view bringSubviewToFront:loader];
    
    if([[[UIDevice currentDevice] systemVersion] floatValue] >= 5.0) {
        wv.scrollView.scrollEnabled = NO;
    }
    else {
        for (id subview in wv.subviews) {
            if ([[subview class] isSubclassOfClass: [UIScrollView class]]) {
                ((UIScrollView *)subview).scrollEnabled=NO;
            }
        }
    }
    
    NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL: [NSURL URLWithString:[[url sharedInstance] get:@""]]];
    wv.delegate = self;
    [wv loadRequest:request];
    
    // Do any additional setup after loading the view from its nib.
}

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    
    if(!isAtLeastiOS7()) {
        [[UINavigationBar appearance] setBackgroundImage:[[UIImage alloc] init] forBarMetrics:UIBarMetricsDefault];
        [[UINavigationBar appearance] setBackgroundColor:[UIColor blackColor]];
    }
}

- (void)viewDidAppear:(BOOL)animated {
    [super viewDidAppear:animated];
    
//    if(isAtLeastiOS7()) {
//        [[UIApplication sharedApplication] setStatusBarStyle:UIStatusBarStyleDefault];
//    }
    
    if(webViewIsLoaded) {
        [wv stringByEvaluatingJavaScriptFromString:[NSString stringWithFormat:@"loader.hide()"]];
        [wv stringByEvaluatingJavaScriptFromString:[NSString stringWithFormat:@"app.onFocus()"]];
    }
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation {
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}

- (BOOL)shouldAutorotate {
    UIDeviceOrientation deviceOrientation = (UIDeviceOrientation) [[UIDevice currentDevice] orientation];
    return (deviceOrientation == UIDeviceOrientationPortrait);
}

- (void)viewDidUnload {
    [self setWv:nil];
    [self setMainView:nil];
    [super viewDidUnload];
}

- (void)loadNotifs {
    if(self.canLoadNotifs) {
        // Load the push notifications
        [wv stringByEvaluatingJavaScriptFromString:[NSString stringWithFormat:@"notif.loadBadge()"]];
        self.canLoadNotifs = NO;
    }
}

- (void)webView:(UIWebView *)webView didFailLoadWithError:(NSError *)error {
    NSLog(@"error: %@", error);
    [loader hide];
}

- (void)webViewDidStartLoad:(UIWebView *)webView {
//    [loader show];
}

- (void)webViewDidFinishLoad:(UIWebView *)webView {
    
    [loader hide];
    
    if(!webViewIsLoaded) {

        self.canLoadNotifs = YES;
        
        if(splashScreen.hidden == NO) {
            [UIView beginAnimations:@"startup_image" context:nil];
            [UIView setAnimationDuration:0.8];
            [UIView setAnimationDelegate:self];
            [UIView setAnimationDidStopSelector:@selector (startupAnimationDone:finished:context:)];
            splashScreen.alpha = 0;
            [UIView commitAnimations];
        }
        
        // Update the CSS Class
        if(isAtLeastiOS7()) {
            [wv stringByEvaluatingJavaScriptFromString:@"app.isAtLeastiOS7()"];
        }
        
        // Update the device_uid
        NSUserDefaults *dict = [NSUserDefaults standardUserDefaults];
        NSString *identifier = [dict stringForKey:@"identifier"];
        [wv stringByEvaluatingJavaScriptFromString:[NSString stringWithFormat:@"customer.device_uid = '%@';", identifier]];
        // Update the push notifications
        [self loadNotifs];
        
        NSString *appVersion = [[[NSBundle mainBundle] infoDictionary] objectForKey:@"CFBundleVersion"];
        [wv stringByEvaluatingJavaScriptFromString:[NSString stringWithFormat:@"app.setVersion('%@')", appVersion]];
        
        NSString *excluded_urls = [wv stringByEvaluatingJavaScriptFromString:@"app.getExcludedUrls();"];
        excludedUrls = [excluded_urls componentsSeparatedByString:@","];
        
        webViewIsLoaded = YES;
        
    }
    
}

#pragma clang diagnostic ignored "-Warc-performSelector-leaks"
- (BOOL)webView:(UIWebView *)webView shouldStartLoadWithRequest:(NSURLRequest *)request navigationType:(UIWebViewNavigationType)navigationType {

    NSString *path = [[request URL] path];
    NSString *host = [[request URL] host];
    NSString *query = [[request URL] query];
    if(query == nil) query = @"";

    NSString *stringUrl = [NSString stringWithFormat:@"%@", [request URL]];
    
    if([stringUrl rangeOfString:@"tel:"].location != NSNotFound) {
        
        NSLog(@"phone number : %@", [request URL]);
        
    }
    else if([stringUrl rangeOfString:@"app:"].location != NSNotFound) {
        
        NSArray *words = [stringUrl componentsSeparatedByString:@":"];
        SEL function = NSSelectorFromString([words lastObject]);
        
        if([self respondsToSelector:function]) {
            [self performSelector:function];
        }
        
        return NO;
        
    }
    else if([stringUrl rangeOfString:@"youtube:"].location != NSNotFound) {
        
        NSArray *words = [stringUrl componentsSeparatedByString:@":"];
        youtubePlayerViewController *youtubePlayer = [[youtubePlayerViewController alloc] init];
        youtubePlayer.videoId = [words lastObject];
        [self presentModalViewController:youtubePlayer animated:YES];
        
        return NO;
        
    }
    else if([stringUrl rangeOfString:@"video:"].location != NSNotFound) {
        NSURL *videoURL = [NSURL URLWithString:[stringUrl stringByReplacingOccurrencesOfString:@"video:" withString:@""]];
        moviePlayerViewController *playerViewController = [[moviePlayerViewController alloc] init];
        NSLog(@"movie URL : %@", [request URL]);
        [playerViewController setVideoURL:videoURL];
        [self presentMoviePlayerViewControllerAnimated:playerViewController];
        
        return NO;
    } else if([stringUrl rangeOfString:@"maps:"].location != NSNotFound) {
        
        NSData *datas = [[wv stringByEvaluatingJavaScriptFromString:@"contact_address"] dataUsingEncoding:NSUTF8StringEncoding];
        NSString *contact_address = [[NSString alloc] initWithData:datas encoding:NSUTF8StringEncoding];
        datas = [[wv stringByEvaluatingJavaScriptFromString:@"contact_name"] dataUsingEncoding:NSUTF8StringEncoding];
        NSString *contact_name = [[NSString alloc] initWithData:datas encoding:NSUTF8StringEncoding];
        
        if(contact_address != nil && ![contact_address isEqualToString:@""]) {
            
            mapsViewController *controller = [[mapsViewController alloc] init];
            NSLog(@"contact_name : %@", contact_name);
            controller.name = contact_name;
            controller.address = contact_address;
            UINavigationController *nav = [[UINavigationController alloc]
                                           initWithRootViewController:controller];
            [self presentViewController:nav animated:YES completion:NULL];
            
        }
        else {
            // Affiche le message
            UIAlertView *alert = [[UIAlertView alloc] initWithTitle:NSLocalizedString(@"Erreur", nil) message:NSLocalizedString(@"We are sorry but our address can't be found in Plans", nil) delegate:self cancelButtonTitle:@"OK" otherButtonTitles:nil];
            [alert show];
        }
        
        return NO;
    }
    else if(path.length > 0) {
        
        if([host rangeOfString:[url sharedInstance].domain].location == NSNotFound) {

            NSString *url = [host stringByAppendingString:path];
            bool openWebview = true;
            for(int idx=0;idx<excludedUrls.count;idx++) {
                if([url hasPrefix:[excludedUrls objectAtIndex:idx]]) openWebview = false;
            }
            
            if(openWebview) {
                webViewController *WebViewController = [[webViewController alloc] init];
                [WebViewController setWebViewUrl:[request URL]];
                WebViewController.delegate = self;
                UINavigationController *nav = [[UINavigationController alloc]
                                               initWithRootViewController:WebViewController];
                
                [self presentViewController:nav animated:YES completion:NULL];
                
                return NO;
                
            }
            
        }
    }
    return YES;
}

- (void)startupAnimationDone:(NSString *)animationID finished:(NSNumber *)finished context:(void *)context {
    
    [splashScreen removeFromSuperview];
    if(isPreview) {
        [wv stringByEvaluatingJavaScriptFromString:[NSString stringWithFormat:@"showHowToClosePreview()"]];
    }
}

- (void)appDidEnterForeground {
    [self loadNotifs];
}

- (void)locationManager:(CLLocationManager *)manager didUpdateToLocation:(CLLocation *)newLocation fromLocation:(CLLocation *)oldLocation {
    
    // Créé les données à poster
    double lat_a = newLocation.coordinate.latitude;
    double lon_a = newLocation.coordinate.longitude;
    NSUserDefaults *dict = [NSUserDefaults standardUserDefaults];
    NSString *device_uid = [dict stringForKey:@"identifier"];
    
    // Créé un dictionnaire avec les données à poster dedans
    NSMutableDictionary *datas = [NSMutableDictionary dictionary];
    [datas setObject:[NSString stringWithFormat:@"%f", lat_a] forKey:@"latitude"];
    [datas setObject:[NSString stringWithFormat:@"%f", lon_a] forKey:@"longitude"];
    [datas setObject:device_uid forKey:@"device_uid"];    
    
    // Post des données
    Request *request = [Request alloc];
    request.delegate = self;
    [request postDatas:datas withUrl:@"push/iphone/updateposition"];

}

- (void)connectionDidFinish:(NSData *)datas {
//    NSString *returnDatas = [[NSString alloc] initWithData:datas encoding:NSUTF8StringEncoding];
//    NSLog(@"returnDatas : %@", returnDatas);
}

- (void)connectionDidFail {
    
}

- (void)closepreview {
    [self dismissModalViewControllerAnimated:YES];
}

- (void)notifsDidShow {
    [[NSNotificationCenter defaultCenter] postNotificationName:@"notifsDidShow" object:nil];
}


@end
