//
//  moviePlayerViewController.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import <MediaPlayer/MediaPlayer.h>
#import "common.h"

@interface moviePlayerViewController : MPMoviePlayerViewController {
    NSURL *videoURL;
}

@property (strong, nonatomic) NSURL *videoURL;

@end
