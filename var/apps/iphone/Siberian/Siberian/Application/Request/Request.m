//
//  Request.m
//  Siberian
//
//  Created by The Tiger App Creator Team on 24/02/14.
//
//

#import "Request.h"

@implementation Request

@synthesize delegate, isSynchronious, webData;

- (void)postDatas:(NSMutableDictionary *)datas withUrl:(NSString *)withUrl {
    
    NSLog(@"url : %@", [[url sharedInstance] get:withUrl]);
    NSLog(@"datas : %@", datas);

    NSString *postString = [NSString string];
    for (id key in datas) {
        postString = [postString stringByAppendingFormat:@"&%@=%@", key, [datas objectForKey:key]];
    }

    // Ajoute l'identifiant du device (utilisé côté serveur)
    postString = [postString stringByAppendingFormat:@"&device_id=%@", @"1"];
    
    
    NSMutableURLRequest *request;
    // Prépare la requête
    request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:[[url sharedInstance] get:withUrl]] cachePolicy:NSURLRequestUseProtocolCachePolicy timeoutInterval:20.0];
    [request setHTTPBody:[postString dataUsingEncoding:NSUTF8StringEncoding]];
    [request setHTTPMethod:@"POST"];
    
    
    if(self.isSynchronious) {
        NSData *returnData = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
        if([delegate respondsToSelector:@selector(connectionDidFinish:)]) {
            [delegate connectionDidFinish:returnData];
        }
    }
    else {
        NSURLConnection *connectionSign=[[NSURLConnection alloc] initWithRequest:request delegate:self];
        
        if (connectionSign) {
            // Prépare les données à récupérer de la requête
            webData = [NSMutableData data];
        }
        else {
            NSLog(@"Error");
        }        
    }
    
}

- (void)postWithUrl:(NSString *)withUrl {
    NSMutableDictionary *datas = [NSMutableDictionary dictionary];
    [self postDatas:datas withUrl:withUrl];
}

- (void)loadImage:(NSString *)withUrl {
    
    NSMutableURLRequest *request;

    // Prépare la requête
    request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:[[url sharedInstance] getImage:withUrl]] cachePolicy:NSURLRequestUseProtocolCachePolicy timeoutInterval:20.0];
    [request setHTTPMethod:@"GET"];

    
    if(self.isSynchronious) {
        NSData *returnData = [NSURLConnection sendSynchronousRequest:request returningResponse:nil error:nil];
        if([delegate respondsToSelector:@selector(connectionDidFinish:)]) {
            [delegate connectionDidFinish:returnData];
        }
    }
    else {
        NSURLConnection *connection=[[NSURLConnection alloc] initWithRequest:request delegate:self];
        
        if (connection) {
            // Prépare les données à récupérer de la requête
            webData = [NSMutableData data];
        }
        else {
            NSLog(@"Error");
        }        
    }
    
}

-(void)connection:(NSURLConnection *)connection didReceiveData:(NSData *)data {
	[webData appendData:data];
}

-(void)connectionDidFinishLoading:(NSURLConnection *)connection {
    NSString *returnString = [[NSString alloc] initWithData:webData encoding:NSUTF8StringEncoding];
    NSLog(@"datas : %@", returnString);
    if([delegate respondsToSelector:@selector(connectionDidFinish:)]) {
        [delegate connectionDidFinish:webData];
    }
}

- (void)connection:(NSURLConnection *)connection didFailWithError:(NSError *)error {
    UIAlertView *alert = [[UIAlertView alloc] initWithTitle:@"Erreur" message:@"Une erreur est survenue lors de l'établissement de la connexion à notre serveur. Merci de vérifier votre connexion Internet." delegate:self cancelButtonTitle:@"OK" otherButtonTitles:nil];
    [alert show];
    
    if([delegate respondsToSelector:@selector(connectionDidFail)]) {
        [delegate connectionDidFail];
    }
}

@end
