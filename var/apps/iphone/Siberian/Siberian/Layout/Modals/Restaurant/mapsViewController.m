//
//  mapsViewController.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "mapsViewController.h"

@interface mapsViewController ()

@end

@implementation mapsViewController
@synthesize mapView, labelHeader;
@synthesize name, address;

- (void)viewWillAppear:(BOOL)animated {
    [super viewWillAppear:animated];
    
    [self prepareLayout];
}

- (void)viewDidUnload
{
    [self setMapView:nil];
    [self setLabelHeader:nil];
    [super viewDidUnload];
    // Release any retained subviews of the main view.
    // e.g. self.myOutlet = nil;
}

- (BOOL)shouldAutorotateToInterfaceOrientation:(UIInterfaceOrientation)interfaceOrientation
{
    return (interfaceOrientation == UIInterfaceOrientationPortrait);
}

- (void) prepareLayout {
    
    self.title = self.name;
    UIBarButtonItem *btnClose = [[UIBarButtonItem alloc]
                                 initWithTitle:NSLocalizedString(@"Done", nil)
                                 style:UIBarButtonItemStyleDone
                                 target:self
                                 action:@selector(back:)];
    self.navigationItem.leftBarButtonItem = btnClose;
    
    UIColor *titleColor = [UIColor whiteColor];
    
    if(isAtLeastiOS7()) {
        self.navigationController.navigationBar.translucent = NO;
        self.navigationController.navigationBar.barTintColor = [UIColor colorWithRed:0.30f green:0.30f blue:0.30f alpha:1.00f];
        self.navigationController.navigationBar.tintColor = [UIColor blackColor];
        [btnClose setTintColor:[UIColor blackColor]];
        titleColor = [UIColor blackColor];
    }

    UILabel *navbarLabel = [[UILabel alloc] initWithFrame:CGRectZero];
    navbarLabel.backgroundColor = [UIColor clearColor];
    navbarLabel.shadowColor = [UIColor clearColor];
    navbarLabel.font = [UIFont boldSystemFontOfSize:20.0f];
    navbarLabel.textAlignment = UITextAlignmentCenter;
    navbarLabel.textColor = titleColor;
    self.navigationItem.titleView = navbarLabel;
    navbarLabel.text = self.name;
    [navbarLabel sizeToFit];
    
    // Affiche les données
    [self showDatas];
}

- (void) showDatas {

    // Ajoute la position du client
    mapView.showsUserLocation = YES;
    
    CLGeocoder *geocoder = [[CLGeocoder alloc] init];
    [geocoder geocodeAddressString:self.address
                 completionHandler:^(NSArray* placemarks, NSError* error){
                     // Vérifie les placemarks
                     if (placemarks && placemarks.count > 0) {
                         
                         // Créé le placemark
                         MKPlacemark *placemark = [[MKPlacemark alloc] initWithPlacemark:[placemarks objectAtIndex:0]];
                         
                         // Définit le zoom
                         MKCoordinateSpan span;
                         span.latitudeDelta=1;
                         span.longitudeDelta=1;
                         
                         // Définit les coordonnées 
                         CLLocationCoordinate2D coordinates = placemark.coordinate;
                         MKCoordinateRegion region;
                         region.span=span;
                         region.center=coordinates;
                         
                         // Ajoute le marker du point de vente
                         MKPointAnnotation *marker = [[MKPointAnnotation alloc] init];
                         marker.coordinate = coordinates;
                         marker.title = self.name;
                         marker.subtitle = self.address;
                         [mapView addAnnotation:marker]; 
                         
                         // Centre la carte sur le point
                         [mapView setRegion:region animated:TRUE];
                         
                     }
                     else {
                         UIAlertView *alert = [[UIAlertView alloc] initWithTitle:@"Erreur" message:NSLocalizedString(@"An error occured while retrieving our address. We're really sorry and encourage you to come back later.", nil) delegate:self cancelButtonTitle:NSLocalizedString(@"OK", nil) otherButtonTitles:nil];
                         [alert show];
                     }
                     
                 }];

    
}

- (IBAction)back:(id)sender {
    [self dismissViewControllerAnimated:YES completion:nil];
}

@end
