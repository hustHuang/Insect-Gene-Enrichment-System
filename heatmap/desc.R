library("gplots")
args <- commandArgs(TRUE);
t1=args[1];
t2=args[2];
t3=args[3];
t4=args[4];
#cat(t1)
library(bigmemory)
t<-dget("/tmp/matrix.desc")
matrix<-attach.big.matrix(t)
k=matrix[t1:t2,t3:t4]
nowtime<-as.integer(Sys.time())
tfn <- paste("/var/www/SGA/heatmap/",nowtime,".png",sep="")
png(filename = tfn,height=750,width=850)
heatmap.2(k, col=redgreen(75), scale="row", key=TRUE, symkey=FALSE, density.info="none", trace="none", cexRow=1.0,margins=c(10,10))
dev.off()
cat(tfn)
