setwd("/var/www/SGA/R_results/GO_enrichment_test/")
library("gplots")
nowtime<-as.integer( Sys.time())
#Imagename <- paste("/var/www/SGA/R_results/GO_enrichment_test/",nowtime,".png",sep="")
tfn <- paste("/var/www/SGA/R_results/GO_enrichment_test/",nowtime,".png",sep="")
test<-read.table("/var/www/SGA/R/matrix2.txt")
test_matrix<-data.matrix(test)
#cat(test_matrix)
cat(test_matrix)
#getwd()
#png("my.png")
png(filename = tfn,width = 480, height = 480, units = "px", pointsize = 12,bg = "white", res = NA, family = "")
heatmap.2(test_matrix, col=redgreen(75), scale="row", key=TRUE, symkey=FALSE, density.info="none", trace="none", cexRow=0.1)
dev.off()
cat(tfn)