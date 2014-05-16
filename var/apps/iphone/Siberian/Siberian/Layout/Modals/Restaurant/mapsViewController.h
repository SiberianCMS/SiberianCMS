//
//  mapsViewController.h
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//
#import <UIKit/UIKit.h>
#import <MapKit/MapKit.h>
#import "common.h"

@interface mapsViewController : UIViewController {
    NSString *name;
    NSString *address;
}

@property (strong, nonatomic) IBOutlet MKMapView *mapView;
@property (strong, nonatomic) IBOutlet UILabel *labelHeader;
@property (strong, nonatomic) NSMutableDictionary *currentSign;
@property (strong, nonatomic) NSMutableDictionary *currentPos;
@property (strong, nonatomic) NSString *name;
@property (strong, nonatomic) NSString *address;

- (IBAction)back:(id)sender;

@end
