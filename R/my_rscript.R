# my_rscript.R
 
args <- commandArgs(TRUE)
a <- as.integer(args[1])
b <- as.integer(args[2])
cat(a + b) 