library(bigmemory)
load("/var/www/SGA/heatmap/test.RData")
tem<-data.matrix(test)
temp<-as.big.matrix(tem, type = NULL, separated = FALSE,  backingfile = NULL, backingpath = NULL, descriptorfile = NULL,  shared=TRUE)
desc<-describe(temp)
dput(desc , file ="/tmp/matrix.desc")
while(1)
{Sys.sleep(6000)}
#repeat i<-1

