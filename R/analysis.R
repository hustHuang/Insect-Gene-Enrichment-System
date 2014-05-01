library("GO.db")
library("annotate")
library("genefilter")
library("GOstats")
library(org.Sc.sgd.db)
library("RamiGO")
args <- commandArgs(TRUE)
genes <- toString(args[1])
tologytype <- toString(args[2])
cutoff <- as.numeric(args[3])
representation <- toString(args[4])
#representation <- 'over'

# Current time to generate filenaem
nowtime<-as.integer( Sys.time())
tfn <- paste("/var/www/SGA/R_results/GO_enrichment_test/table_",nowtime,".txt",sep="")
nafn <- paste("/var/www/SGA/R_results/GO_enrichment_test/network_annot_",nowtime,".txt",sep="")
nrfn <- paste("/var/www/SGA/R_results/GO_enrichment_test/network_relation_",nowtime,".txt",sep="")
#tfn <- paste("/tmp/table_",nowtime,".txt",sep="")
#nafn <- paste("/tmp/network_annot_",nowtime,".txt",sep="")
#nrfn <- paste("/tmp/network_relation_",nowtime,".txt",sep="")

segene <- unlist(strsplit(genes, split=","))
len <- length(segene)
genelist <- readLines("/var/www/SGA/R/result.orf")
params = new ("GOHyperGParams", geneIds=segene, universeGeneIds = genelist, annotation = "org.Sc.sgd.db", ontology= tologytype, pvalueCutoff= cutoff, conditional=FALSE,testDirection=representation)
over = hyperGTest(params)
sover<-summary(over)
dg<-goDag(over)
nodeatt<-nodeatt<-nodeData(dg,as.vector(sover[,1]))

nodeatt.collapsed <- lapply(nodeatt, function(x) sapply(x, paste,collapse=', '))
nodeatt.collapsed <- as.data.frame(nodeatt.collapsed)
snewover<- as.data.frame(t(nodeatt.collapsed))
sover[,"geneIds"]<-snewover[,"geneIds"]

dsv <- summary(over,htmlLinks = TRUE)

# Write table data dsv to file
# write.table(dsv,file=tfn,quote=FALSE,sep = "\t",row.names=TRUE,col.names=TRUE)
write.table(sover,file=tfn,quote=FALSE,sep="\t",row.names = FALSE,col.name=FALSE)
goIDs1<-sover[,1]
colorID1<-rainbow(length(goIDs1))
dd1 <- getAmigoTree(goIDs=goIDs1,color=colorID1,filename="example",picType="dot",saveResult=FALSE)
tt1 <- readAmigoDot(object=dd1)

write.table(tt1@annot[,1:6],file=nafn,quote=FALSE,sep = "\t",row.names=FALSE,col.names=FALSE)
write.table(tt1@relations[,1:2],file=nrfn,quote=FALSE,sep = "\t",row.names=TRUE,col.names=FALSE)
# write.table(tt1@leaves[,1:2],file=leaves,quote=FALSE,sep = "\t",row.names=FALSE,col.names=FALSE)

cat(nowtime)
