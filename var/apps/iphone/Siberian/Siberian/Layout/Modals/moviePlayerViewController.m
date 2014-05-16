//
//  moviePlayerViewController.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "moviePlayerViewController.h"

@interface moviePlayerViewController ()

@end

@implementation moviePlayerViewController

@synthesize videoURL;

- (void)viewDidLoad {
    
    [super viewDidLoad];
    
    [[NSNotificationCenter defaultCenter] addObserver:self
                                             selector:@selector(handleMPMoviePlayerPlaybackDidFinish:)
                                                 name:MPMoviePlayerPlaybackDidFinishNotification
                                               object:nil];
    self.moviePlayer.contentURL = self.videoURL;
    self.moviePlayer.controlStyle = MPMovieControlStyleFullscreen;
    [self.moviePlayer play];
}

- (void)viewDidUnload {
    [self setVideoURL:nil];
    [super viewDidUnload];
}

- (void)didReceiveMemoryWarning {
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (void)handleMPMoviePlayerPlaybackDidFinish:(NSNotification *)notification {
    
    NSDictionary *notificationUserInfo = [notification userInfo];
    NSNumber *resultValue = [notificationUserInfo objectForKey:MPMoviePlayerPlaybackDidFinishReasonUserInfoKey];
    MPMovieFinishReason reason = [resultValue intValue];
    if (reason == MPMovieFinishReasonPlaybackError) {
        NSError *mediaPlayerError = [notificationUserInfo objectForKey:@"error"];
        if (mediaPlayerError) {
            NSLog(@"playback failed with error description: %@", [mediaPlayerError localizedDescription]);
        }
        else {
            NSLog(@"playback failed without any given reason");
        }
    }
}

@end
