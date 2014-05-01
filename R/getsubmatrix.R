setwd("/var/www/SGA/R/")
getwd()
test<-read.table("matrixbig.txt")
test_matrix<-data.matrix(test)
#cat("here")
k=test_matrix[1:100,1:100]
cat(k)
write.table(k,"/var/www/SGA/R_results/GO_enrichment_test/My_sub_matrix.txt");