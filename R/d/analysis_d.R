#library(GeneAnswers)
data(DOLite)
genelist<-POST[["g"]]
cutoff=as.numeric(POST[["c"]])
#genelist<-"YKR054C,YNR016C,YMR207C,YJR066W"
#genelist<-"10594,32,31,11127,9371,3797,4112,4115,4113,645864,286514,139422,4114,4109,139604,158809,4103,4110,4107,4108,728269"
segene <- unlist(strsplit(genelist, split=","))
nowtime<-as.integer(Sys.time())
z <- geneAnswersBuilder(segene, 'org.Hs.eg.db', categoryType='DOLITE', testType='hyperG', pvalueT=0.5, geneExpressionProfile=NULL, verbose=FALSE)
doinfo<-getEnrichmentInfo(z)
info<-getEnrichmentInfo(z)
info[,1]<-row.names(doinfo)
info[,2]<-doinfo[,6]
info[,3]<-doinfo[,1]
info[,4]<-doinfo[,5]
info[,5]<-doinfo[,2]
info[,6]<-doinfo[,3]
info[,7]<-doinfo[,4]
w=DOLiteTerm[row.names(doinfo)]
info[,"Term"]<-w
##get the geneID by intersection between input and map
r<-nrow(info)
for(i in 1:r)
{
t<-as.data.frame(DOLite[info[,1]][i])
st<-intersect(segene,t[,1])

#cat(st)
#cat("\r\n")
if(length(st)==0)
{st<-"NA"}
else{
st<-paste(st,collapse=",")

}
info[i,"geneIds"]<-st;
}
##

#info$id<-DOLite[info[,1]][1]

tfn <- paste("/tmp/table_",nowtime,".txt",sep="")
write.table(info,file=tfn,row.names=FALSE,col.names=FALSE,quote=FALSE,sep = "\t")
cat(nowtime)
