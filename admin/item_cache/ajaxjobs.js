/* 	
 	Open Media Collectors Database
	Copyright (C) 2001,2002 by Jason Pell

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

function Job(id, url, onUpdateFunc)
{
	this.id = id;
	this.url = url;
	this.onUpdateFunc = onUpdateFunc;
		
	this.reset();
}

Job.prototype.cancel = function (reason)
{
    this.cancelled = true;
    this.exception = reason;
}

Job.prototype.reset = function ()
{
	this.totalItems = 0;
	this.processedItems = 0;
	this.cancelled = false;
	this.started = false;
	this.exception = null;
	this.finished = false;
	this.executeCount = 0;
}

Job.prototype.percentage = function ()
{
	if(this.started)
	{
		if(this.totalItems > 0 && this.processedItems > 0)
		{
			if(this.processedItems >= this.totalItems)
				return 100;
			else
				return Math.round(Number(this.processedItems) / (Number(this.totalItems) / 100));
		}
		else if(this.totalItems == 0)
		{
			return 100;	
		}
	}
	else
	{
		return Number(0);
	}
}

Job.prototype.outOfTen = function()
{
	return Math.round(this.percentage() / 10);
}

var jobList = new Array();

function getJobById(id)
{
	for(var i=0; i<jobList.length; i++)
	{
		if(jobList[i].id = id)
		{
			return jobList[i];
		}
	}
	
	//else
	return null;
}

/**
If a job exists with the current jobs id, the existing job is discarded in favour of this one,
regardless of whether its still executing or not.
*/
function addJobToList(job)
{
	var isFound = false;
	
	for(var i=0; i<jobList.length; i++)
	{
		if(jobList[i].id = job.id)
		{
			jobList[i] = job;
		}
	}
	
	if(!isFound)
	{
		jobList.push(job);
	}
}


function onAdminJobComplete(job, response)
{
	if(response.responseXML)
	{
		var docRoot = response.responseXML.getElementsByTagName("job");
		if(docRoot.length>0)
		{
			var resultTag = docRoot[0].getElementsByTagName("result");
			
			job.executeCount++;
			
			var status = resultTag[0].getElementsByTagName("status")[0].firstChild.nodeValue;
			var processed = resultTag[0].getElementsByTagName("processed")[0].firstChild.nodeValue;
			var unprocessed = resultTag[0].getElementsByTagName("unprocessed")[0].firstChild.nodeValue;

			if(status == 'SUCCESS')
			{
				if(!job.started)
				{
					job.started = true;
					job.totalItems = Number(processed) + Number(unprocessed);
				}
				
				if(processed > 0)
				{
					job.processedItems += Number(processed);
				}
	
				if(unprocessed == 0)
				{
					job.finished = true;
				}
				else // unprocessed > 0
				{
					var batchsize = docRoot[0].getElementsByTagName("params")[0].getElementsByTagName("batchsize")[0].firstChild.nodeValue;
					if(processed < batchsize)
					{
						job.cancelled = true;
						job.exception = 'All items in a batch have not been processed';
					}
				}
			}
			else
			{
				job.cancelled = true;
				job.exception = 'Job did not complete successfully';
				
				if(unprocessed > 0)
				{
					job.exception += "<br />("+unprocessed + " unprocessed)";
				}
			}
		}
		else
		{
			job.cancelled = true;
			job.exception = 'Job did not return a valid response';
		}
	}
	else
	{
		job.cancelled = true;
		job.exception = 'Job did not return a valid response';
	}
	
	// at this point call the update widget process
	if(job.onUpdateFunc)
	{
		job.onUpdateFunc(job);
	}
	
	// do not continue if cancelled
	if(!job.cancelled && !job.finished)
	{
		setTimeout(function(){executeAdminJob(job);}, 1000);
	}
}

function executeAdminJob(job)
{
	var handlerFunc = function(response)
	{
    	onAdminJobComplete(job, response);
	}

	var errFunc = function(t, e)
	{
    	alert(e);
	}

	if(!job.started)
	{
		addJobToList(job);
	}
	
	// just in case job.finished / job.cancelled gets set between calling setTimeout and executing this function
	if(!job.cancelled && !job.finished)
	{	
		new Ajax.Request(job.url, {asynchronous:true, method:'get', onException:errFunc, onComplete:handlerFunc, onFailure:errFunc});
	}
}